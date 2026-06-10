<?php

declare(strict_types=1);

use fan\core\runtime\error_handler_registrar;
use PHPUnit\Framework\TestCase;

final class ErrorHandlerRegistrarTest extends TestCase
{
    public function testSourceOwnsNativeErrorHandlerRegistration(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/runtime/error_handler_registrar.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class error_handler_registrar', $source);
        $this->assertStringContainsString('return set_error_handler($handler);', $source);
    }

    public function testRegistrarIsCallable(): void
    {
        $registrar = new error_handler_registrar();

        $this->assertIsCallable($registrar);
    }
}
