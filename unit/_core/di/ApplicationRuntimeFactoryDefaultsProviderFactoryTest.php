<?php

declare(strict_types=1);

use fan\core\di\application_registry_defaults_provider_factory;
use fan\core\di\application_runtime_factory_defaults_provider;
use fan\core\di\application_runtime_factory_defaults_provider_factory;
use fan\core\di\application_runtime_factory_provider;
use PHPUnit\Framework\TestCase;

final class ApplicationRuntimeFactoryDefaultsProviderFactoryTest extends TestCase
{
    public function testFactoryCreatesApplicationRuntimeFactoryDefaultsProvider(): void
    {
        $adapterRegistry = (new application_registry_defaults_provider_factory())()->applicationAdapterRegistry();
        $defaultsProvider = (new application_runtime_factory_defaults_provider_factory())($adapterRegistry);

        $this->assertInstanceOf(application_runtime_factory_defaults_provider::class, $defaultsProvider);
        $this->assertInstanceOf(application_runtime_factory_provider::class, $defaultsProvider->applicationRuntimeFactoryProvider());
    }

    public function testFactoryAcceptsInjectedRuntimeFactoryDefaults(): void
    {
        $adapterRegistry = (new application_registry_defaults_provider_factory())()->applicationAdapterRegistry();
        $calls = [
            'provider' => 0,
            'configured' => 0,
            'request' => 0,
            'phpArray' => 0,
            'serializer' => 0,
        ];
        $defaultsProvider = (new application_runtime_factory_defaults_provider_factory(
            function (callable ...$factories) use (&$calls): application_runtime_factory_provider {
                ++$calls['provider'];

                return new application_runtime_factory_provider(...$factories);
            },
            function () use (&$calls): callable {
                ++$calls['configured'];

                return static fn(): object => (object)['name' => 'configured'];
            },
            function () use (&$calls): callable {
                ++$calls['request'];

                return static fn(): object => (object)['name' => 'request-input'];
            },
            function ($registry) use ($adapterRegistry, &$calls): callable {
                ++$calls['phpArray'];
                $this->assertSame($adapterRegistry, $registry);

                return static fn(string $path, mixed $default = null): string => 'php-array';
            },
            function ($registry) use ($adapterRegistry, &$calls): callable {
                ++$calls['serializer'];
                $this->assertSame($adapterRegistry, $registry);

                return static fn(object $warningCapture): object => (object)['name' => 'serializer'];
            }
        ))($adapterRegistry);

        $runtimeFactoryProvider = $defaultsProvider->applicationRuntimeFactoryProvider();

        $this->assertSame(
            ['provider' => 1, 'configured' => 1, 'request' => 1, 'phpArray' => 1, 'serializer' => 1],
            $calls
        );
        $this->assertSame('configured', $runtimeFactoryProvider->configuredConstructionBoundary()()->name);
        $this->assertSame('request-input', $runtimeFactoryProvider->requestInputFactory()()->name);
        $this->assertSame('php-array', $runtimeFactoryProvider->phpArrayFileLoader()('config.php'));
        $this->assertSame('serializer', $runtimeFactoryProvider->serializerOperationsFactory()(new stdClass())->name);
    }
}
