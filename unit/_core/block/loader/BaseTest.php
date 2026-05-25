<?php

declare(strict_types=1);

use fan\core\block\loader\base;
use FanTest\_core\SourceFileContractTestCase;
use fan\core\adapter\data_loader;


class BlockLoaderBaseTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/block/loader/base.php';

    public function testSetJsonHtmlAndTextMergeIntoView(): void
    {
        $block = new BlockLoaderBaseProbe();

        $this->assertSame($block, $block->setJson(['a' => 1]));
        $this->assertSame($block, $block->setJson(['b' => 2]));
        $this->assertSame(['a' => 1, 'b' => 2], $block->view->json);

        $block->setHtml('Hello')->setHtml(' world');
        $block->setText('Alpha')->setText(' beta');

        $this->assertSame('Hello world', $block->view->html);
        $this->assertSame('Alpha beta', $block->view->text);
    }

    public function testGetDataCachesLoaderPayload(): void
    {
        $loader = new BlockLoaderDataLoaderDouble(['id' => 7]);
        $block = new BlockLoaderBaseProbe($loader);

        $this->assertSame(['id' => 7], $block->getData());
        $loader->data = ['id' => 8];
        $this->assertSame(['id' => 7], $block->getData());
        $this->assertSame(1, $loader->getDataCalls);
    }

    public function testDefaultDataLoaderReceivesInjectedInputAndJsonServices(): void
    {
        $json = new BlockLoaderJsonDouble();
        $block = new BlockLoaderBaseProbe(
            null,
            new BlockLoaderRequestInputDouble(['id' => 9]),
            $json
        );

        $loader = $block->getDataLoader();

        $this->assertSame(['id' => 9], $loader->getData());
        $this->assertSame('encoded-loader-output', $loader->setText('done')->send(false));
        $this->assertSame([[
            'json' => [],
            'text' => 'done',
            'html' => '',
        ]], $json->payloads);
    }

    public function testOutcomeReturnsViewArrayAndInitIsAllowed(): void
    {
        $block = new BlockLoaderBaseProbe();
        $block->setJson(['ok' => true], false)->setText('done', false);

        $this->assertTrue($block->checkRunInit());
        $this->assertSame([
            'json' => ['ok' => true],
            'html' => '',
            'text' => 'done',
        ], $block->getOutcome());
    }

    public function testSourceNoLongerCallsContainerServiceDirectly(): void
    {
        $source = $this->sourceCode();

        $this->assertStringNotContainsString('containerService(', $source);
        $this->assertStringNotContainsString('new \\fan\\project\\adapter\\data_loader', $source);
        $this->assertStringNotContainsString('adduceToArray(', $source);
        $this->assertStringNotContainsString('array_merge_recursive_alt(', $source);
        $this->assertStringContainsString('$this->arrayAdducer()', $source);
        $this->assertStringContainsString('$this->recursiveMerger()', $source);
        $this->assertStringContainsString('$this->dataLoaderService()', $source);
        $this->assertStringNotContainsString('private function arrayAdducer', $source);
        $this->assertStringNotContainsString('private function recursiveMerger', $source);
    }
}

class BlockLoaderBaseProbe extends base
{
    public BlockLoaderViewDouble $view;

    public function __construct(
        ?BlockLoaderDataLoaderDouble $loader = null,
        private ?object $input = null,
        private ?object $json = null
    ) {
        $this->view = new BlockLoaderViewDouble();
        $this->setBlockDependencies([
            'dataLoaderFactory' => fn(): object => $loader ?? new data_loader(
                $this->requestInputService(),
                $this->jsonService(),
                static fn(mixed $value): array => is_array($value) ? $value : [$value],
                static fn(mixed ...$values): array => array_replace_recursive(...$values)
            ),
            'arrayAdducer' => static fn(mixed $value): array => is_array($value) ? $value : [$value],
            'recursiveMerger' => static fn(mixed ...$values): array => array_replace_recursive(...$values),
        ]);
    }

    protected function requestInputService(): object
    {
        return $this->input ?? new BlockLoaderRequestInputDouble([]);
    }

    protected function jsonService(mixed ...$arguments): object
    {
        return $this->json ?? new BlockLoaderJsonDouble();
    }
}

final class BlockLoaderViewDouble
{
    public array $json = [];

    public string $html = '';

    public string $text = '';

    public function toArray(): array
    {
        return [
            'json' => $this->json,
            'html' => $this->html,
            'text' => $this->text,
        ];
    }
}

final class BlockLoaderDataLoaderDouble
{
    public int $getDataCalls = 0;

    public function __construct(public array $data)
    {
    }

    public function getData(): array
    {
        $this->getDataCalls++;

        return $this->data;
    }
}

final class BlockLoaderRequestInputDouble
{
    public function __construct(private array $request)
    {
    }

    public function request(): array
    {
        return $this->request;
    }
}

final class BlockLoaderJsonDouble
{
    public array $payloads = [];

    public function encode(mixed $payload): string
    {
        $this->payloads[] = $payload;

        return 'encoded-loader-output';
    }
}
