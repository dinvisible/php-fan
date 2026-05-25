<?php

declare(strict_types=1);

use fan\core\adapter\php_template_file;
use PHPUnit\Framework\TestCase;

final class PhpTemplateFileTest extends TestCase
{
    public function testStaticRendererRendersTemplateWithVariables(): void
    {
        $path = $this->createTemplateFile('<?php echo $greeting . ", " . $name;');

        try {
            $this->assertSame(
                'Hello, installer',
                php_template_file::render($path, ['greeting' => 'Hello', 'name' => 'installer'])
            );
        } finally {
            unlink($path);
        }
    }

    public function testRendererThrowsForUnreadableTemplate(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('PHP template file is not readable at "/tmp/php-fan-missing-template.php".');

        (new php_template_file())->renderFile('/tmp/php-fan-missing-template.php');
    }

    public function testRendererUsesInjectedCallbacks(): void
    {
        $readableCalls = [];
        $executorCalls = [];
        $renderer = new php_template_file(
            static function (string $path) use (&$readableCalls): bool {
                $readableCalls[] = $path;

                return $path === '/tmp/template.php';
            },
            static function (string $path, array $variables) use (&$executorCalls): string {
                $executorCalls[] = [$path, $variables];

                return 'rendered:' . $variables['name'];
            }
        );

        $this->assertSame('rendered:adapter', $renderer('/tmp/template.php', ['name' => 'adapter']));
        $this->assertSame(['/tmp/template.php'], $readableCalls);
        $this->assertSame([['/tmp/template.php', ['name' => 'adapter']]], $executorCalls);
    }

    public function testDefaultExecutorCleansOutputBufferWhenTemplateThrows(): void
    {
        $path = $this->createTemplateFile('<?php echo "partial"; throw new \RuntimeException("template failed");');
        $level = ob_get_level();

        try {
            try {
                php_template_file::render($path);
                $this->fail('Expected template exception.');
            } catch (\RuntimeException $exception) {
                $this->assertSame('template failed', $exception->getMessage());
                $this->assertSame($level, ob_get_level());
            }
        } finally {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }
            unlink($path);
        }
    }

    public function testSourceOwnsPhpTemplateStaticAdapterBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/adapter/php_template_file.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('private \Closure $isReadable;', $source);
        $this->assertStringContainsString('private \Closure $templateExecutor;', $source);
        $this->assertStringContainsString('public function __construct(?callable $isReadable = null, ?callable $templateExecutor = null)', $source);
        $this->assertStringContainsString('public function __invoke(string $path, array $variables = []): string', $source);
        $this->assertStringContainsString('return (new self())->renderFile($path, $variables);', $source);
        $this->assertStringContainsString('return ($this->templateExecutor)($path, $variables);', $source);
        $this->assertStringContainsString('include $path;', $source);
        $this->assertStringNotContainsString('if (!is_readable($path))', $source);
    }

    private function createTemplateFile(string $source): string
    {
        $path = tempnam(sys_get_temp_dir(), 'php-fan-template-');
        $this->assertIsString($path);
        file_put_contents($path, $source);

        return $path;
    }
}
