<?php

declare(strict_types=1);

use fan\core\runtime\error_handler_setup;
use PHPUnit\Framework\TestCase;

final class ErrorHandlerSetupTest extends TestCase
{
    public function testSetupAppliesDefaultTimezoneAndRegistersHandler(): void
    {
        $settings = new ErrorHandlerSetupTestSettings('');
        $registeredHandler = null;
        $setup = new error_handler_setup(
            $settings,
            static function (callable $handler) use (&$registeredHandler): void {
                $registeredHandler = $handler;
            }
        );
        $handler = static fn(): null => null;

        $setup($handler);

        $this->assertSame([['date.timezone', 'Europe/Helsinki']], $settings->sets);
        $this->assertSame($handler, $registeredHandler);
    }

    public function testSetupKeepsConfiguredTimezone(): void
    {
        $settings = new ErrorHandlerSetupTestSettings('UTC');
        $registeredHandler = null;
        $setup = new error_handler_setup(
            $settings,
            static function (callable $handler) use (&$registeredHandler): void {
                $registeredHandler = $handler;
            }
        );
        $handler = static fn(): null => null;

        $setup($handler, 'Europe/Warsaw');

        $this->assertSame([], $settings->sets);
        $this->assertSame($handler, $registeredHandler);
    }

    public function testSetupDoesNotOwnConcreteRuntimeBoundaries(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/runtime/error_handler_setup.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class error_handler_setup', $source);
        $this->assertStringContainsString('public function __construct(object $phpRuntimeSettings, callable $errorHandlerRegistrar)', $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/php_runtime_settings.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/error_handler_registrar.php';", $source);
        $this->assertStringNotContainsString('new php_runtime_settings()', $source);
        $this->assertStringNotContainsString('new error_handler_registrar()', $source);
        $this->assertStringNotContainsString('defaultPhpRuntimeSettings', $source);
        $this->assertStringNotContainsString('defaultErrorHandlerRegistrar', $source);
        $this->assertStringNotContainsString('ini_get(', $source);
        $this->assertStringNotContainsString('ini_set(', $source);
        $this->assertStringNotContainsString('set_error_handler(', $source);
    }
}

final class ErrorHandlerSetupTestSettings
{
    public array $sets = [];

    public function __construct(private string|false $timezone)
    {
    }

    public function get(string $name): string|false
    {
        return $name === 'date.timezone' ? $this->timezone : false;
    }

    public function set(string $name, string $value): void
    {
        $this->sets[] = [$name, $value];
    }
}
