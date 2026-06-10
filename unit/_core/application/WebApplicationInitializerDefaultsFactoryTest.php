<?php

declare(strict_types=1);

use fan\core\bootstrap\request_runner;
use fan\core\bootstrap\web_application_initializer;
use fan\core\di\web_application_initializer_defaults_factory;
use PHPUnit\Framework\TestCase;

final class WebApplicationInitializerDefaultsFactoryTest extends TestCase
{
    public function testFactoryCreatesWebApplicationInitializer(): void
    {
        $factory = new web_application_initializer_defaults_factory(
            static fn(): web_application_initializer => new web_application_initializer(
                new request_runner(
                    static fn(): object => new WebApplicationInitializerDefaultsFactoryApplicationStub(),
                    static fn(object $application): callable => static function (): void {
                    }
                ),
                '/project/conf/bootstrap.php',
                '/project/htdocs'
            )
        );

        $this->assertInstanceOf(web_application_initializer::class, $factory());
    }

    public function testFactoryOwnsWebApplicationInitializerDefaultDependencies(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/web_application_initializer_defaults_factory.php');
        $entrypointSource = file_get_contents(dirname(__DIR__, 3) . '/htdocs/index.php');
        $composerAutoloadSource = file_get_contents(dirname(__DIR__, 3) . '/tools/composer_autoload.php');

        $this->assertIsString($source);
        $this->assertIsString($entrypointSource);
        $this->assertIsString($composerAutoloadSource);
        $this->assertStringContainsString('final class web_application_initializer_defaults_factory', $source);
        $this->assertStringContainsString('private \Closure $webApplicationInitializerFactory;', $source);
        $this->assertStringContainsString('public function __construct(?callable $webApplicationInitializerFactory = null)', $source);
        $this->assertStringContainsString('$requestRunnerDefaults = new request_runner_defaults_factory();', $source);
        $this->assertStringContainsString('new web_application_initializer(', $source);
        $this->assertStringContainsString('$requestRunnerDefaults()', $source);
        $this->assertStringContainsString('null,', $source);
        $this->assertStringContainsString("dirname(__DIR__, 2) . '/htdocs'", $source);
        $this->assertStringContainsString('$this->webApplicationInitializerFactory = \Closure::fromCallable($webApplicationInitializerFactory);', $source);
        $this->assertStringContainsString('public function __invoke(): web_application_initializer', $source);
        $this->assertStringContainsString('return ($this->webApplicationInitializerFactory)();', $source);
        $this->assertStringNotContainsString('require_once', $source);

        $this->assertStringContainsString("require_once __DIR__ . '/../vendor/autoload.php';", $entrypointSource);
        $this->assertStringContainsString('((new web_application_initializer_defaults_factory())())->run();', $entrypointSource);
        $this->assertStringNotContainsString('new request_runner_defaults_factory()', $entrypointSource);
        $this->assertFileDoesNotExist(dirname(__DIR__, 3) . '/htdocs/autoload.php');

        $this->assertStringContainsString("'fan\\\\core\\\\bootstrap\\\\' => \$phpFanRoot . '/_core/application/'", $composerAutoloadSource);
        $this->assertStringContainsString("if (str_contains(\$relativeClass, 'factory'))", $composerAutoloadSource);
        $this->assertStringContainsString("if (!function_exists('array_val'))", $composerAutoloadSource);
        $this->assertStringContainsString("require_once \$phpFanRoot . '/_core/functions.php';", $composerAutoloadSource);
    }
}

final class WebApplicationInitializerDefaultsFactoryApplicationStub
{
    public function run(?string $configPath, bool $isEcho = true, ?callable $errorHandler = null): string
    {
        return 'ok';
    }

    public function context(): object
    {
        return new stdClass();
    }
}
