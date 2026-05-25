<?php

declare(strict_types=1);

use fan\core\di\application_deferred_service_factory_provider;
use fan\core\di\application_deferred_service_factory_provider_factory;
use PHPUnit\Framework\TestCase;

final class ApplicationDeferredServiceFactoryProviderFactoryTest extends TestCase
{
    public function testFactoryCreatesApplicationDeferredServiceFactoryProvider(): void
    {
        $provider = (new application_deferred_service_factory_provider_factory())();

        $this->assertInstanceOf(application_deferred_service_factory_provider::class, $provider);
    }

    public function testFactoryAcceptsInjectedDeferredServiceFactoryProvider(): void
    {
        $expectedProvider = new application_deferred_service_factory_provider();
        $provider = (new application_deferred_service_factory_provider_factory(
            static fn(): application_deferred_service_factory_provider => $expectedProvider
        ))();

        $this->assertSame($expectedProvider, $provider);
    }}
