<?php

declare(strict_types=1);

use fan\core\adapter\zend_autoloader_loader;
use FanTest\core\SourceFileContractTestCase;

final class AdapterZendAutoloaderLoaderTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/adapter/zend_autoloader_loader.php';

    public function testLoadDelegatesToInjectedLoader(): void
    {
        $calls = [];
        $loader = new zend_autoloader_loader(static function (string $zendPath) use (&$calls): string {
            $calls[] = $zendPath;

            return 'loaded';
        });

        $this->assertSame('loaded', $loader->load('/opt/Zend'));
        $this->assertSame(['/opt/Zend'], $calls);
    }

    public function testDefaultAdapterCallIsIsolatedInLoaderBoundary(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('zend_autoloader::load($zendPath)', $source);
        $this->assertStringContainsString('$loader', $source);
    }
}
