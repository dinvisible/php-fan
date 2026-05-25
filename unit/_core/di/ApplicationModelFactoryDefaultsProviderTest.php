<?php

declare(strict_types=1);

use fan\core\di\application_adapter_registry;
use fan\core\di\application_adapter_registry_defaults_provider;
use fan\core\di\application_model_factory_defaults_provider;
use fan\core\di\application_model_factory_provider;
use fan\core\di\entity_designer_factory;
use fan\core\di\entity_encapsulant_factory;
use fan\core\di\model_entity_factory;
use fan\core\di\model_entity_exception_factory;
use fan\core\di\model_request_factory;
use fan\core\di\model_row_factory;
use fan\core\di\model_row_exception_factory;
use fan\core\di\model_rowset_factory;
use PHPUnit\Framework\TestCase;
use fan\core\adapter\reflection_class_factory;
use fan\core\di\application_registry_defaults_provider_factory;


final class ApplicationModelFactoryDefaultsProviderTest extends TestCase
{
    public function testProviderCreatesApplicationModelFactoryProviderWithDefaultFactories(): void
    {
        $adapterRegistry = (new application_registry_defaults_provider_factory())()->applicationAdapterRegistry();
        $defaultsProvider = self::defaultsProvider($adapterRegistry);

        $modelFactoryProvider = $defaultsProvider->applicationModelFactoryProvider();
        $configuredServiceFactory = static fn(string $className, array $arguments): object => new stdClass();
        $serializerOperations = new stdClass();

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
    }    private static function defaultsProvider(application_adapter_registry $adapterRegistry): application_model_factory_defaults_provider
    {
        $modelRequestFileStorage = $adapterRegistry->modelRequestFileStorage();

        return new application_model_factory_defaults_provider(
            static fn(callable ...$factories): application_model_factory_provider => new application_model_factory_provider(
                ...$factories
            ),
            static fn(callable $configuredServiceFactory): callable => new entity_designer_factory($configuredServiceFactory),
            static fn(callable $configuredServiceFactory): callable => new entity_encapsulant_factory($configuredServiceFactory),
            static fn(callable $configuredServiceFactory): callable => new model_entity_factory(
                $configuredServiceFactory,
                new model_entity_exception_factory($configuredServiceFactory)
            ),
            static fn(object $serializerOperations, callable $configuredServiceFactory): callable => new model_row_factory(
                $serializerOperations,
                $configuredServiceFactory,
                new model_row_exception_factory($configuredServiceFactory)
            ),
            static fn(object $serializerOperations, callable $configuredServiceFactory): callable => new model_rowset_factory(
                $serializerOperations,
                $configuredServiceFactory
            ),
            static fn(callable $configuredServiceFactory): callable => new model_request_factory(
                $configuredServiceFactory,
                $modelRequestFileStorage
            )
        );
    }
}
