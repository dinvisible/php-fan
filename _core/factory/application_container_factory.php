<?php

declare(strict_types=1);

namespace fan\core\di;

/**
 * Builds the default application container without tying the service graph to a
 * static registry lifecycle.
 */
final class application_container_factory
{
    private application_service_factory_bundle $factoryBundle;

    private application_container_dependency_bundle $dependencyBundle;

    public function __construct(
        application_service_factory_bundle $factoryBundle,
        application_container_dependency_bundle $dependencyBundle
    ) {
        $this->factoryBundle = $factoryBundle;
        $this->dependencyBundle = $dependencyBundle;
    }

    public function create(): container
    {
        $factoryBundle = $this->factoryBundle;
        $dependencyBundle = $this->dependencyBundle;
        $supportServiceRegistrar = $dependencyBundle->supportServiceRegistrar;
        $serviceGraphRegistrar = $dependencyBundle->serviceGraphRegistrar;
        $deferredServiceFactoryProvider = $dependencyBundle->deferredServiceFactoryProvider;
        $dependencyProvider = $dependencyBundle->dependencyProvider;
        $adapterRegistry = $dependencyProvider->applicationAdapterRegistry();
        $stateRegistry = $dependencyProvider->applicationStateRegistry();
        $modelFactoryProvider = $dependencyProvider->applicationModelFactoryProvider();
        $serviceFactoryRegistry = $dependencyProvider->applicationServiceFactoryRegistry();
        $warningCapture = $adapterRegistry->warningCapture();
        $serializerOperations = ($factoryBundle->serializerOperationsFactory)($warningCapture);
        if (!is_object($serializerOperations)) {
            throw new \RuntimeException('Serializer operations factory must return an object.');
        }
        $cacheEngineFactory = $deferredServiceFactoryProvider->cacheEngineFactory(
            $factoryBundle->cacheEngineFactory,
            $serializerOperations,
            $factoryBundle->configuredServiceFactory,
            $adapterRegistry,
            $serviceFactoryRegistry
        );
        $sessionEngineFactory = $deferredServiceFactoryProvider->sessionEngineFactory(
            $factoryBundle->sessionEngineFactory,
            $factoryBundle->configuredServiceFactory,
            $adapterRegistry,
            $serviceFactoryRegistry
        );
        $userEngineFactory = $deferredServiceFactoryProvider->userEngineFactory(
            $factoryBundle->userEngineFactory,
            $serializerOperations,
            $factoryBundle->configuredServiceFactory,
            $serviceFactoryRegistry
        );
        $modelRowFactory = $deferredServiceFactoryProvider->modelRowFactory(
            $factoryBundle->modelRowFactory,
            $serializerOperations,
            $factoryBundle->configuredServiceFactory,
            $modelFactoryProvider
        );
        $modelRowsetFactory = $deferredServiceFactoryProvider->modelRowsetFactory(
            $factoryBundle->modelRowsetFactory,
            $serializerOperations,
            $factoryBundle->configuredServiceFactory,
            $modelFactoryProvider
        );
        $container = new container();
        $adapterRegistry->register($container, $factoryBundle->phpArrayFileLoader);
        $stateRegistry->register($container);
        $supportServiceRegistrar->register(
            $container,
            $serializerOperations,
            $factoryBundle->requestInputFactory,
            $factoryBundle->bootstrapOperationsFactory,
            $factoryBundle->bootstrapRuntimeServiceFactory,
            $factoryBundle->serviceEngineFactory,
            $factoryBundle->blockExceptionFactory
        );
        $serviceGraphRegistrationContext = application_service_graph_registration_context::fromBundles(
            $dependencyBundle,
            $factoryBundle,
            $cacheEngineFactory,
            $sessionEngineFactory,
            $userEngineFactory,
            $modelRowFactory,
            $modelRowsetFactory
        );
        $serviceGraphRegistrar->register(
            $container,
            $serviceGraphRegistrationContext
        );

        return $container;
    }

}
