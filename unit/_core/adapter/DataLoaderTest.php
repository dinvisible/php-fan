<?php

declare(strict_types=1);

use fan\core\adapter\data_loader;
use PHPUnit\Framework\TestCase;


class AdapterDataLoaderTest extends TestCase
{
    public function testGetDataReadsRequestThroughInputAdapter(): void
    {
        $loader = $this->loader(
            new AdapterDataLoaderInputDouble(['id' => '42']),
            new AdapterDataLoaderJsonDouble(),
            new AdapterDataLoaderHeaderWriterDouble()
        );

        $this->assertSame(['id' => '42'], $loader->getData());
    }

    public function testSendEncodesCurrentPayloadThroughInjectedJsonService(): void
    {
        $json = new AdapterDataLoaderJsonDouble();
        $loader = $this->loader(new AdapterDataLoaderInputDouble([]), $json, new AdapterDataLoaderHeaderWriterDouble());

        $content = $loader
            ->setJson(['answer' => 42])
            ->setText('plain')
            ->setHtml('<b>html</b>')
            ->send(false);

        $this->assertSame('encoded-payload', $content);
        $this->assertSame([[
            'json' => ['answer' => 42],
            'text' => 'plain',
            'html' => '<b>html</b>',
        ]], $json->payloads);
    }

    public function testSendWritesJsonHeaderThroughInjectedWriter(): void
    {
        $json = new AdapterDataLoaderJsonDouble();
        $writer = new AdapterDataLoaderHeaderWriterDouble();
        $loader = $this->loader(new AdapterDataLoaderInputDouble([]), $json, $writer);

        $this->assertSame('encoded-payload', $loader->send());
        $this->assertSame([
            ['Content-Type: application/json; charset=utf-8', true, 0],
        ], $writer->headers);
    }

    public function testSendSkipsHeaderWhenAlreadySent(): void
    {
        $json = new AdapterDataLoaderJsonDouble();
        $writer = new AdapterDataLoaderHeaderWriterDouble(true);
        $loader = $this->loader(new AdapterDataLoaderInputDouble([]), $json, $writer);

        $this->assertSame('encoded-payload', $loader->send());
        $this->assertSame([], $writer->headers);
    }

    public function testSetJsonUsesInjectedArrayOperations(): void
    {
        $arrayAdducerCalls = [];
        $recursiveMergerCalls = [];
        $json = new AdapterDataLoaderJsonDouble();
        $loader = new data_loader(
            new AdapterDataLoaderInputDouble([]),
            $json,
            static function (mixed $value) use (&$arrayAdducerCalls): array {
                $arrayAdducerCalls[] = $value;

                return ['normalized' => $value];
            },
            static function (mixed ...$values) use (&$recursiveMergerCalls): array {
                $recursiveMergerCalls[] = $values;

                return ['merged' => $values];
            },
            new AdapterDataLoaderHeaderWriterDouble()
        );

        $loader->setJson('payload')->setJson(['next' => true]);
        $loader->send(false);

        $this->assertSame(['payload', ['next' => true]], $arrayAdducerCalls);
        $this->assertSame([
            [[], ['normalized' => 'payload']],
            [['merged' => [[], ['normalized' => 'payload']]], ['normalized' => ['next' => true]]],
        ], $recursiveMergerCalls);
        $this->assertSame([[
            'json' => ['merged' => [['merged' => [[], ['normalized' => 'payload']]], ['normalized' => ['next' => true]]]],
            'text' => '',
            'html' => '',
        ]], $json->payloads);
    }

    public function testSourceUsesInjectedBoundaries(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/adapter/data_loader.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('callable $arrayAdducer', $source);
        $this->assertStringContainsString('callable $recursiveMerger', $source);
        $this->assertStringContainsString('$this->arrayAdducer', $source);
        $this->assertStringContainsString('$this->recursiveMerger', $source);
        $this->assertStringContainsString('private ?object $headerWriter = null', $source);
        $this->assertStringContainsString('$this->headerWriter()->send(', $source);
        $this->assertStringContainsString('private function headerWriter(): object', $source);
        $this->assertStringNotContainsString('adduceToArray(', $source);
        $this->assertStringNotContainsString('array_merge_recursive_alt(', $source);
        $this->assertStringNotContainsString('header(', $source);
        $this->assertStringNotContainsString('headers_sent(', $source);
    }

    private function loader(
        object $input,
        object $json,
        ?object $headerWriter = null
    ): data_loader {
        return new data_loader(
            $input,
            $json,
            static fn(mixed $value): array => is_array($value) ? $value : [$value],
            static fn(mixed ...$values): array => array_replace_recursive(...$values),
            $headerWriter
        );
    }
}

final class AdapterDataLoaderInputDouble
{
    public function __construct(private array $request)
    {
    }

    public function request(): array
    {
        return $this->request;
    }
}

final class AdapterDataLoaderJsonDouble
{
    public array $payloads = [];

    public function encode(mixed $payload): string
    {
        $this->payloads[] = $payload;

        return 'encoded-payload';
    }
}

final class AdapterDataLoaderHeaderWriterDouble
{
    public array $headers = [];

    public function __construct(private bool $sent = false)
    {
    }

    public function sent(?string &$file = null, ?int &$line = null): bool
    {
        return $this->sent;
    }

    public function send(string $header, bool $replace = true, int $responseCode = 0): void
    {
        $this->headers[] = [$header, $replace, $responseCode];
    }
}
