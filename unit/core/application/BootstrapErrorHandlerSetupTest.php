<?php

declare(strict_types=1);

use fan\core\bootstrap\bootstrap_error_handler_setup;
use fan\core\bootstrap\context;
use fan\core\bootstrap\state;
use PHPUnit\Framework\TestCase;
use fan\core\di\context_defaults_factory;


final class BootstrapErrorHandlerSetupTest extends TestCase
{
    public function testSetupSetsConfiguredBootstrapLogDirAndDelegatesHandlerRegistration(): void
    {
        $state = new state();
        $state->setConfig([
            'bootstrap' => [
                'global_path' => [
                    'bootstrap_log' => '{ROOT}/logs/bootstrap',
                ],
            ],
        ]);
        $state->setReplacement(['{ROOT}' => '/tmp/php-fan']);
        $calls = [];
        $handler = static fn(): null => null;
        $context = new context(
            $state,
            errorHandlerSetup: static function (callable $handler, string $defaultTimezone) use (&$calls): void {
                $calls[] = [$handler, $defaultTimezone];
            },
            defaultFactoriesFactory: self::defaultFactoriesFactory()
        );

        (new bootstrap_error_handler_setup())($context, $handler);

        $this->assertSame('/tmp/php-fan/logs/bootstrap', $state->logDir());
        $this->assertSame([[$handler, 'Europe/Helsinki']], $calls);
    }

    public function testSetupKeepsDefaultLogDirWhenConfigPathIsMissing(): void
    {
        $state = new state();
        $calls = [];
        $handler = static fn(): null => null;
        $context = new context(
            $state,
            errorHandlerSetup: static function (callable $handler, string $defaultTimezone) use (&$calls): void {
                $calls[] = [$handler, $defaultTimezone];
            },
            defaultFactoriesFactory: self::defaultFactoriesFactory()
        );

        (new bootstrap_error_handler_setup())($context, $handler);

        $this->assertSame('{CORE_DIR}/../logs/bootstrap_log/', $state->logDir());
        $this->assertSame([[$handler, 'Europe/Helsinki']], $calls);
    }

    public function testSetupHasNoStaticBootstrapDependency(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/application/bootstrap_error_handler_setup.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class bootstrap_error_handler_setup', $source);
        $this->assertStringContainsString('public function __invoke(context $context, callable $handler): void', $source);
        $this->assertStringContainsString('$context->setupErrorHandler($handler)', $source);
        $this->assertStringNotContainsString('\bootstrap::', $source);
        $this->assertStringNotContainsString('set_error_handler(', $source);
    }

    private static function defaultFactoriesFactory(): callable
    {
        return new context_defaults_factory();
    }
}
