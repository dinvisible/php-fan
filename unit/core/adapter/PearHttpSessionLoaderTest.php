<?php

declare(strict_types=1);

use fan\core\adapter\pear_http_session_loader;
use fan\core\adapter\pear_http_session;
use FanTest\core\SourceFileContractTestCase;

final class AdapterPearHttpSessionLoaderTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/adapter/pear_http_session_loader.php';

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

        $this->assertInstanceOf(pear_http_session::class, (new pear_http_session_loader())->load());
        $this->assertStringContainsString('new pear_http_session()', $source);
        $this->assertStringNotContainsString('ensureAvailable', $source);
        $this->assertStringContainsString('$loader', $source);
    }
}
