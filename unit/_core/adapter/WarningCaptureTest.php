<?php

declare(strict_types=1);

use fan\core\adapter\warning_capture;
use PHPUnit\Framework\TestCase;

final class WarningCaptureTest extends TestCase
{
    public function testRunCapturesWarningsAndReturnsOperationResult(): void
    {
        $capture = new warning_capture();
        $warnings = [];

        $result = $capture->run(
            static function (): string {
                trigger_error('captured warning', E_USER_WARNING);

                return 'done';
            },
            static function (string $message, int $severity) use (&$warnings): void {
                $warnings[] = [$message, $severity];
            }
        );

        $this->assertSame('done', $result);
        $this->assertSame([['captured warning', E_USER_WARNING]], $warnings);
    }

    public function testSourceOwnsNativeErrorHandlerBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/adapter/warning_capture.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class warning_capture', $source);
        $this->assertStringContainsString('set_error_handler(', $source);
        $this->assertStringContainsString('restore_error_handler();', $source);
    }
}
