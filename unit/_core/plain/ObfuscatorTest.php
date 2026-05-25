<?php

declare(strict_types=1);

use fan\core\plain\obfuscator;
use fan\core\service\plain;
use FanTest\_core\SourceFileContractTestCase;

class PlainObfuscatorTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/plain/obfuscator.php';

    public function testCssContentIsReadFromConfiguredObfuscatorAndHeadersAreApplied(): void
    {
        $plain = new PlainObfuscatorPlainDouble(['reqKey' => 'css']);
        $obfuscatorService = new PlainObfuscatorServiceDouble([
            'style.css' => 'body { color: red; }',
        ]);

        $controller = $this->controller($plain, $obfuscatorService, new PlainObfuscatorRequestDouble('style.css'));

        $this->assertSame('body { color: red; }', $controller->getCss());
        $this->assertSame([['style.css']], $obfuscatorService->fileDataCalls);
        $this->assertSame([['style.css', 20]], $obfuscatorService->headersCalls);
        $this->assertSame([
            'contentType' => 'text/css',
            'filename' => 'style.css',
            'length' => 20,
        ], $plain->headers);
    }

    public function testJsUsesSameContentPipeline(): void
    {
        $plain = new PlainObfuscatorPlainDouble(['reqKey' => 'js']);
        $obfuscatorService = new PlainObfuscatorServiceDouble([
            'app.js' => 'alert(1);',
        ], 'application/javascript');

        $controller = $this->controller($plain, $obfuscatorService, new PlainObfuscatorRequestDouble('app.js'));

        $this->assertSame('alert(1);', $controller->getJs());
        $this->assertSame([
            'contentType' => 'application/javascript',
            'filename' => 'app.js',
            'length' => 9,
        ], $plain->headers);
    }

    public function testSourceUsesInjectedObfuscatorFactoryDirectly(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('return $obfuscatorFactory($type);', $source);
        $this->assertStringNotContainsString('call_user_func', $source);
    }

    private function controller(plain $plain, object $obfuscatorService, object $request): obfuscator
    {
        return new obfuscator($plain, 'asset', static fn(string $type): object => $obfuscatorService, $request);
    }
}

final class PlainObfuscatorPlainDouble extends plain
{
    public array $headers = [];

    public function __construct(private array $handleData)
    {
    }

    public function getHandleData(): array
    {
        return $this->handleData;
    }

    public function setHeaders(array $headers): static
    {
        $this->headers = $headers;

        return $this;
    }
}

final class PlainObfuscatorServiceDouble
{
    public array $fileDataCalls = [];

    public array $headersCalls = [];

    public function __construct(private array $files, private string $contentType = 'text/css')
    {
    }

    public function getFileData(string $name): string|false
    {
        $this->fileDataCalls[] = [$name];

        return $this->files[$name] ?? false;
    }

    public function getHeaders(string $name, ?int $length = null): array
    {
        $this->headersCalls[] = [$name, $length];

        return [
            'contentType' => $this->contentType,
            'filename' => $name,
            'length' => $length,
        ];
    }
}

final class PlainObfuscatorRequestDouble
{
    public function __construct(private string $name)
    {
    }

    public function get(int|string $key, mixed $default = null): mixed
    {
        return $key === 1 ? $this->name : $default;
    }
}
