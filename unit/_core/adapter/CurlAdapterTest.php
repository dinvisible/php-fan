<?php

declare(strict_types=1);

use fan\core\adapter\curl_adapter;
use PHPUnit\Framework\TestCase;

final class CurlAdapterTest extends TestCase
{
    public function testAdapterWrapsNativeCurlHandleOperations(): void
    {
        $adapter = new curl_adapter();
        $handle = $adapter->init('https://example.test');

        $this->assertIsObject($handle);
        $this->assertTrue($adapter->setOption($handle, CURLOPT_RETURNTRANSFER, true));
        $this->assertIsArray($adapter->getInfo($handle));
        $this->assertIsString($adapter->error($handle));
        $adapter->close($handle);
    }

    public function testSourceOwnsNativeCurlBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/adapter/curl_adapter.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class curl_adapter', $source);
        $this->assertStringContainsString('curl_init($url)', $source);
        $this->assertStringContainsString('curl_setopt($handle, $key, $value)', $source);
        $this->assertStringContainsString('curl_exec($handle)', $source);
        $this->assertStringContainsString('curl_getinfo($handle)', $source);
        $this->assertStringContainsString('curl_error($handle)', $source);
        $this->assertStringContainsString('curl_close($handle)', $source);
    }
}
