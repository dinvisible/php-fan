<?php

declare(strict_types=1);

use fan\core\di\application_deferred_service_factory_provider;
use fan\core\di\application_factory_provider_defaults_provider;
use fan\core\di\application_factory_provider_defaults_provider_factory;
use fan\core\di\application_model_factory_defaults_provider_factory;
use fan\core\di\application_model_factory_provider;
use fan\core\di\application_runtime_factory_defaults_provider_factory;
use fan\core\di\application_runtime_factory_provider;
use fan\core\di\application_service_factory_defaults_provider_factory;
use fan\core\di\service_factory_registry;
use PHPUnit\Framework\TestCase;
use fan\core\di\application_registry_defaults_provider_factory;

final class ApplicationFactoryProviderDefaultsProviderFactoryTest extends TestCase
{
    public function testFactoryCreatesApplicationFactoryProviderDefaultsProvider(): void
    {
        $adapterRegistry = (new application_registry_defaults_provider_factory())()->applicationAdapterRegistry();
        $provider = (new application_factory_provider_defaults_provider_factory())($adapterRegistry);

        $this->assertInstanceOf(application_factory_provider_defaults_provider::class, $provider);
        $this->assertInstanceOf(application_model_factory_provider::class, $provider->applicationModelFactoryProvider());
        $this->assertInstanceOf(service_factory_registry::class, $provider->applicationServiceFactoryRegistry());
        $this->assertInstanceOf(application_runtime_factory_provider::class, $provider->applicationRuntimeFactoryProvider());
        $this->assertInstanceOf(application_deferred_service_factory_provider::class, $provider->applicationDeferredServiceFactoryProvider());
    }

    public function testFactoryAcceptsInjectedFactoryProviderDefaults(): void
    {
        $adapterRegistry = (new application_registry_defaults_provider_factory())()->applicationAdapterRegistry();
        $calls = [
            'model' => 0,
            'service' => 0,
            'runtime' => 0,
            'deferred' => 0,
        ];
        $provider = (new application_factory_provider_defaults_provider_factory(
            function ($registry) use ($adapterRegistry, &$calls) {
                ++$calls['model'];
                $this->assertSame($adapterRegistry, $registry);

                return (new application_model_factory_defaults_provider_factory())($registry);
            },
            function ($registry) use ($adapterRegistry, &$calls) {
                ++$calls['service'];
                $this->assertSame($adapterRegistry, $registry);

                return (new application_service_factory_defaults_provider_factory())($registry);
            },
            function ($registry) use ($adapterRegistry, &$calls) {
                ++$calls['runtime'];
                $this->assertSame($adapterRegistry, $registry);

                return (new application_runtime_factory_defaults_provider_factory())($registry);
            },
            function () use (&$calls): application_deferred_service_factory_provider {
                ++$calls['deferred'];

                return new application_deferred_service_factory_provider();
            }
        ))($adapterRegistry);

        $this->assertSame(['model' => 1, 'service' => 1, 'runtime' => 1, 'deferred' => 1], $calls);
        $this->assertInstanceOf(application_model_factory_provider::class, $provider->applicationModelFactoryProvider());
        $this->assertInstanceOf(service_factory_registry::class, $provider->applicationServiceFactoryRegistry());
        $this->assertInstanceOf(application_runtime_factory_provider::class, $provider->applicationRuntimeFactoryProvider());
        $this->assertInstanceOf(application_deferred_service_factory_provider::class, $provider->applicationDeferredServiceFactoryProvider());
    }
}
