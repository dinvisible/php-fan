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

    public function testFactoryUsesInjectedConfiguredConstructionProviders(): void
    {
        $adapterRegistry = (new application_registry_defaults_provider_factory())()->applicationAdapterRegistry();
        $configuredServiceFactoryProviderCalls = 0;
        $classInstantiatorProviderCalls = 0;
        $receivedClassInstantiator = null;
        $defaultsProvider = (new application_runtime_factory_defaults_provider_factory(
            configuredServiceFactoryProvider: static function (callable $classInstantiator) use (&$configuredServiceFactoryProviderCalls, &$receivedClassInstantiator): callable {
                ++$configuredServiceFactoryProviderCalls;
                $receivedClassInstantiator = $classInstantiator;

                return static fn(): object => (object)['name' => 'configured-provider'];
            },
            classInstantiatorProvider: static function () use (&$classInstantiatorProviderCalls): callable {
                ++$classInstantiatorProviderCalls;

                return static fn(): object => (object)['name' => 'instantiated-provider'];
            }
        ))($adapterRegistry);

        $runtimeFactoryProvider = $defaultsProvider->applicationRuntimeFactoryProvider();

        $this->assertSame(1, $configuredServiceFactoryProviderCalls);
        $this->assertSame(1, $classInstantiatorProviderCalls);
        $this->assertIsCallable($receivedClassInstantiator);
        $this->assertSame('configured-provider', $runtimeFactoryProvider->configuredConstructionBoundary()()->name);
    }

    public function testSourceSplitsConfiguredConstructionDefaultProviders(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/factory/application_runtime_factory_defaults_provider_factory.php');
        $configuredServiceProviderSource = file_get_contents(dirname(__DIR__, 3) . '/core/factory/application_runtime_configured_service_provider.php');
        $classInstantiatorProviderSource = file_get_contents(dirname(__DIR__, 3) . '/core/factory/application_runtime_class_instantiator_provider.php');

        $this->assertIsString($source);
        $this->assertIsString($configuredServiceProviderSource);
        $this->assertIsString($classInstantiatorProviderSource);
        $this->assertStringContainsString('private \Closure $configuredServiceFactoryProvider;', $source);
        $this->assertStringContainsString('private \Closure $classInstantiatorProvider;', $source);
        $this->assertStringContainsString('?callable $configuredServiceFactoryProvider = null,', $source);
        $this->assertStringContainsString('?callable $classInstantiatorProvider = null', $source);
        $this->assertStringContainsString('$this->configuredServiceFactoryProvider = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->classInstantiatorProvider = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('?? new application_runtime_configured_service_provider()', $source);
        $this->assertStringContainsString('?? new application_runtime_class_instantiator_provider()', $source);
        $this->assertStringContainsString('?? fn(): callable => ($this->configuredServiceFactoryProvider)(($this->classInstantiatorProvider)())', $source);
        $this->assertStringNotContainsString('new configured_service_factory(', $source);
        $this->assertStringNotContainsString('new configured_class_instantiator(', $source);
        $this->assertStringContainsString('final class application_runtime_configured_service_provider', $configuredServiceProviderSource);
        $this->assertStringContainsString('return new configured_service_factory($classInstantiator);', $configuredServiceProviderSource);
        $this->assertStringContainsString('final class application_runtime_class_instantiator_provider', $classInstantiatorProviderSource);
        $this->assertStringContainsString('return new configured_class_instantiator(new reflection_class_factory());', $classInstantiatorProviderSource);
    }
}
