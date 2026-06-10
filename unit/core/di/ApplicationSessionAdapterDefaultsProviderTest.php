<?php

declare(strict_types=1);

use fan\core\di\application_session_adapter_defaults_provider;
use PHPUnit\Framework\TestCase;
use fan\core\adapter\pear_http_session_loader;
use fan\core\di\container_interface;


final class ApplicationSessionAdapterDefaultsProviderTest extends TestCase
{
    public function testProviderCreatesSessionAdapterDefaults(): void
    {
        $nativeSession = new ApplicationSessionAdapterDefaultsProviderNativeSessionDouble();
        $pearHttpSession = new ApplicationSessionAdapterDefaultsProviderPearHttpSessionDouble();
        $provider = new application_session_adapter_defaults_provider(
            $nativeSession,
            $pearHttpSession
        );
        $container = $this->createStub(container_interface::class);

        $factories = [
            'pearHttpSessionLoaderFactory' => pear_http_session_loader::class,
        ];

        $this->assertSame($nativeSession, $provider->nativeSession());
        $this->assertSame($pearHttpSession, $provider->pearHttpSession());

        foreach ($factories as $method => $expectedClass) {
            $factory = $provider->{$method}();

            $this->assertInstanceOf(\Closure::class, \Closure::fromCallable($factory));
            $this->assertInstanceOf($expectedClass, $factory($container));
        }
    }

    public function testProviderAcceptsInjectedSessionAdapterFactories(): void
    {
        $pearLoader = (object)['name' => 'pear-loader'];
        $provider = new application_session_adapter_defaults_provider(
            new ApplicationSessionAdapterDefaultsProviderNativeSessionDouble(),
            new ApplicationSessionAdapterDefaultsProviderPearHttpSessionDouble(),
            static fn(container_interface $container): object => $pearLoader
        );
        $container = $this->createStub(container_interface::class);

        $this->assertSame($pearLoader, $provider->pearHttpSessionLoaderFactory()($container));
    }

}

final class ApplicationSessionAdapterDefaultsProviderNativeSessionDouble
{
}

final class ApplicationSessionAdapterDefaultsProviderPearHttpSessionDouble
{
}
