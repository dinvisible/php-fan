<?php

declare(strict_types=1);

use fan\core\adapter\header_writer;
use PHPUnit\Framework\TestCase;

final class HeaderWriterTest extends TestCase
{
    public function testSourceOwnsNativeHeaderBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/adapter/header_writer.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class header_writer', $source);
        $this->assertStringContainsString('return headers_sent($file, $line)', $source);
        $this->assertStringContainsString('header($header, $replace, $responseCode)', $source);
    }

    public function testWriterExposesSendMethod(): void
    {
        $writer = new header_writer();

        $this->assertTrue(method_exists($writer, 'send'));
    }
}
