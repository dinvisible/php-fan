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
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/factory/web_application_initializer_defaults_factory.php');
        $entrypointSource = file_get_contents(dirname(__DIR__, 3) . '/htdocs/index.php');
        $composerAutoloadSource = file_get_contents(dirname(__DIR__, 3) . '/tools/composer_autoload.php');
        $composerConfig = json_decode(
            (string)file_get_contents(dirname(__DIR__, 3) . '/composer.json'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

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

        $this->assertContains('core/functions.php', $composerConfig['autoload']['files']);
        $this->assertSame(['core/application/', 'core/factory/'], $composerConfig['autoload']['psr-4']['fan\\core\\bootstrap\\']);
        $this->assertSame(['core/di/', 'core/factory/'], $composerConfig['autoload']['psr-4']['fan\\core\\di\\']);
        $this->assertSame(['core/adapter/', 'core/factory/adapter/'], $composerConfig['autoload']['psr-4']['fan\\core\\adapter\\']);
        $this->assertSame(['core/runtime/', 'core/factory/runtime/'], $composerConfig['autoload']['psr-4']['fan\\core\\runtime\\']);

        $this->assertStringContainsString('$projectPrefix = \'fan\\\\project\\\\\';', $composerAutoloadSource);
        $this->assertStringNotContainsString('$phpFanRoot', $composerAutoloadSource);
        $this->assertStringNotContainsString('$bootstrapApplicationRoots', $composerAutoloadSource);
        $this->assertStringNotContainsString('$factoryRoots', $composerAutoloadSource);
        $this->assertStringNotContainsString("if (!function_exists('array_val'))", $composerAutoloadSource);
        $this->assertStringNotContainsString('require_once', $composerAutoloadSource);
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
