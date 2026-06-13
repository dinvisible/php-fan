<?php

declare(strict_types=1);

use fan\core\adapter\twig_template_service;
use FanTest\core\SourceFileContractTestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class AdapterTwigTemplateServiceTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/adapter/twig_template_service.php';

    public function testTemplateFileUsesInjectedTwigAvailabilityCheck(): void
    {
        $templatePath = $this->temporaryTemplatePath();
        file_put_contents($templatePath, 'Hello {{ name }}');
        $checkedClasses = [];
        $service = new twig_template_service(
            static function (string $className) use (&$checkedClasses): bool {
                $checkedClasses[] = $className;

                return $className !== Environment::class;
            }
        );
        $template = $service->get($templatePath);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Twig is not installed.');

        try {
            $template->fetch();
        } finally {
            $this->assertSame([ArrayLoader::class, Environment::class], $checkedClasses);
            unlink($templatePath);
        }
    }

    public function testTemplateReadabilityIsCheckedBeforeTwigAvailability(): void
    {
        $templatePath = $this->temporaryTemplatePath();
        $readablePaths = [];
        $checkedClasses = [];
        $service = new twig_template_service(
            static function (string $className) use (&$checkedClasses): bool {
                $checkedClasses[] = $className;

                return true;
            },
            static function (string $path) use (&$readablePaths): bool {
                $readablePaths[] = $path;

                return false;
            },
            static fn(string $path): string => throw new RuntimeException('Template reader should not be called.')
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Twig template file is not readable at "' . $templatePath . '".');

        try {
            $service->get($templatePath)->fetch();
        } finally {
            $this->assertSame([$templatePath], $readablePaths);
            $this->assertSame([], $checkedClasses);
        }
    }

    public function testTemplateFileUsesInjectedReader(): void
    {
        $templatePath = $this->temporaryTemplatePath();
        $readablePaths = [];
        $readPaths = [];
        $service = new twig_template_service(
            static fn(string $className): bool => true,
            static function (string $path) use (&$readablePaths): bool {
                $readablePaths[] = $path;

                return true;
            },
            static function (string $path) use (&$readPaths): string {
                $readPaths[] = $path;

                return 'Hello {{ name }}';
            }
        );
        $template = $service->get($templatePath);
        $template->assign('name', 'Fan');

        $this->assertSame('Hello Fan', $template->fetch());
        $this->assertSame([$templatePath], $readablePaths);
        $this->assertSame([$templatePath], $readPaths);
    }

    public function testSourceKeepsTwigAvailabilityBehindNamedBoundary(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('private \Closure $twigClassExists;', $source);
        $this->assertStringContainsString('private \Closure $isReadable;', $source);
        $this->assertStringContainsString('private \Closure $fileReader;', $source);
        $this->assertStringContainsString('?callable $twigClassExists = null', $source);
        $this->assertStringContainsString('?callable $isReadable = null', $source);
        $this->assertStringContainsString('?callable $fileReader = null', $source);
        $this->assertStringContainsString('private function twigClassesAvailable(): bool', $source);
        $this->assertStringContainsString('private function twigClassExists(string $className): bool', $source);
        $this->assertStringContainsString('private function isReadable(string $path): bool', $source);
        $this->assertStringContainsString('private function readFile(string $path): string|false', $source);
        $this->assertStringContainsString('if (!$this->twigClassesAvailable())', $source);
        $this->assertStringContainsString('if (!$this->isReadable($this->templatePath))', $source);
        $this->assertStringContainsString('(string)$this->readFile($this->templatePath)', $source);
        $this->assertStringNotContainsString('!class_exists(ArrayLoader::class) || !class_exists(Environment::class) || !class_exists(TwigFunction::class)', $source);
        $this->assertStringNotContainsString('if (!is_readable($this->templatePath))', $source);
        $this->assertStringNotContainsString('(string)file_get_contents($this->templatePath)', $source);
    }

    private function temporaryTemplatePath(): string
    {
        return sys_get_temp_dir() . '/fan_twig_template_' . bin2hex(random_bytes(4)) . '.twig';
    }
}
