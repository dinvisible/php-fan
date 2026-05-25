<?php

declare(strict_types=1);

use fan\core\di\application_model_factory_defaults_provider;
use fan\core\di\application_model_factory_defaults_provider_factory;
use fan\core\di\application_model_factory_provider;
use fan\core\di\entity_designer_factory;
use fan\core\di\entity_encapsulant_factory;
use fan\core\di\model_entity_factory;
use fan\core\di\model_request_factory;
use fan\core\di\model_row_factory;
use fan\core\di\model_rowset_factory;
use PHPUnit\Framework\TestCase;
use fan\core\di\application_registry_defaults_provider_factory;

final class ApplicationModelFactoryDefaultsProviderFactoryTest extends TestCase
{
    public function testFactoryCreatesApplicationModelFactoryDefaultsProvider(): void
    {
        $adapterRegistry = (new application_registry_defaults_provider_factory())()->applicationAdapterRegistry();
        $defaultsProvider = (new application_model_factory_defaults_provider_factory())($adapterRegistry);
        $modelFactoryProvider = $defaultsProvider->applicationModelFactoryProvider();
        $configuredServiceFactory = static fn(string $className, array $arguments): object => new stdClass();
        $serializerOperations = new stdClass();

        $this->assertInstanceOf(application_model_factory_defaults_provider::class, $defaultsProvider);
        $this->assertInstanceOf(application_model_factory_provider::class, $modelFactoryProvider);
        $this->assertInstanceOf(entity_designer_factory::class, $modelFactoryProvider->entityDesignerFactory($configuredServiceFactory));
        $this->assertInstanceOf(entity_encapsulant_factory::class, $modelFactoryProvider->entityEncapsulantFactory($configuredServiceFactory));
        $this->assertInstanceOf(model_entity_factory::class, $modelFactoryProvider->modelEntityFactory($configuredServiceFactory));
        $this->assertInstanceOf(model_row_factory::class, $modelFactoryProvider->modelRowFactory($serializerOperations, $configuredServiceFactory));
        $this->assertInstanceOf(model_rowset_factory::class, $modelFactoryProvider->modelRowsetFactory($serializerOperations, $configuredServiceFactory));

        $requestFactory = $modelFactoryProvider->modelRequestFactory($configuredServiceFactory);
        $fileStorageProperty = new ReflectionProperty(model_request_factory::class, 'fileStorage');

        $this->assertInstanceOf(model_request_factory::class, $requestFactory);
        $this->assertSame($adapterRegistry->modelRequestFileStorage(), $fileStorageProperty->getValue($requestFactory));
    }

    public function testFactoryAcceptsInjectedModelFactoryDefaults(): void
    {
        $adapterRegistry = (new application_registry_defaults_provider_factory())()->applicationAdapterRegistry();
        $modelFactoryProviderFactoryCallCount = 0;
        $defaultsProvider = (new application_model_factory_defaults_provider_factory(
            function (callable ...$factories) use (&$modelFactoryProviderFactoryCallCount): application_model_factory_provider {
                ++$modelFactoryProviderFactoryCallCount;

                return new application_model_factory_provider(...$factories);
            },
            self::configuredFactoryMarker('entity-designer'),
            self::configuredFactoryMarker('entity-encapsulant'),
            self::configuredFactoryMarker('model-entity'),
            static fn(object $serializerOperations, callable $configuredServiceFactory): callable => static fn(): object => (object)['name' => 'model-row'],
            static fn(object $serializerOperations, callable $configuredServiceFactory): callable => static fn(): object => (object)['name' => 'model-rowset'],
            function (object $modelRequestFileStorage) use ($adapterRegistry): callable {
                $this->assertSame($adapterRegistry->modelRequestFileStorage(), $modelRequestFileStorage);

                return static fn(callable $configuredServiceFactory): callable => static fn(): object => (object)['name' => 'model-request'];
            }
        ))($adapterRegistry);

        $modelFactoryProvider = $defaultsProvider->applicationModelFactoryProvider();
        $serializerOperations = new stdClass();

        $this->assertSame(1, $modelFactoryProviderFactoryCallCount);
        $this->assertSame('entity-designer', $modelFactoryProvider->entityDesignerFactory(self::configuredServiceFactory())()->name);
        $this->assertSame('entity-encapsulant', $modelFactoryProvider->entityEncapsulantFactory(self::configuredServiceFactory())()->name);
        $this->assertSame('model-entity', $modelFactoryProvider->modelEntityFactory(self::configuredServiceFactory())()->name);
        $this->assertSame('model-row', $modelFactoryProvider->modelRowFactory($serializerOperations, self::configuredServiceFactory())()->name);
        $this->assertSame('model-rowset', $modelFactoryProvider->modelRowsetFactory($serializerOperations, self::configuredServiceFactory())()->name);
        $this->assertSame('model-request', $modelFactoryProvider->modelRequestFactory(self::configuredServiceFactory())()->name);
    }
    private static function configuredFactoryMarker(string $name): callable
    {
        return static fn(callable $configuredServiceFactory): callable => static fn(): object => (object)['name' => $name];
    }

    private static function configuredServiceFactory(): callable
    {
        return static fn(string $className, array $arguments = []): object => new stdClass();
    }
}
