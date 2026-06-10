<?php

declare(strict_types=1);

use fan\core\adapter\cookie_writer;
use PHPUnit\Framework\TestCase;

final class CookieWriterTest extends TestCase
{
    public function testSourceOwnsNativeSetcookieBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/adapter/cookie_writer.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class cookie_writer', $source);
        $this->assertStringContainsString('return setcookie(', $source);
    }

    public function testWriterIsCallableBoundaryObject(): void
    {
        $writer = new cookie_writer();

        $this->assertTrue(method_exists($writer, 'write'));
    }
}
