<?php

declare(strict_types=1);

use fan\core\adapter\pear_http_session_loader;
use FanTest\_core\SourceFileContractTestCase;

final class AdapterPearHttpSessionLoaderTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/adapter/pear_http_session_loader.php';

    public function testLoadDelegatesToInjectedLoader(): void
    {
        $calls = 0;
        $loader = new pear_http_session_loader(static function () use (&$calls): string {
            $calls++;

            return 'loaded';
        });

        $this->assertSame('loaded', $loader->load());
        $this->assertSame(1, $calls);
    }

    public function testDefaultAdapterCallIsIsolatedInLoaderBoundary(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('pear_http_session::ensureAvailable()', $source);
        $this->assertStringContainsString('$loader', $source);
    }
}
