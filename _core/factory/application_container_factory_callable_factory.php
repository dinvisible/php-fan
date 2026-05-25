<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\bootstrap\context;
use fan\core\di\application_container_dependency_bundle;
use fan\core\di\application_container_dependency_provider;
use fan\core\di\application_container_factory;
use fan\core\di\application_service_factory_bundle;
use fan\core\di\application_service_factory_options;
use fan\core\di\application_service_graph_registration_context;
use fan\core\di\container;


final class application_container_factory_callable_factory
{
    private \Closure $containerFactoryFactory;

    private \Closure $applicationContainerFactoryProvider;

    public function __construct(
        ?callable $containerFactoryFactory = null,
        ?callable $applicationContainerFactoryProvider = null
    )
    {
        $this->applicationContainerFactoryProvider = \Closure::fromCallable(
            $applicationContainerFactoryProvider
                ?? static fn(
                    application_service_factory_bundle $factoryBundle,
                    application_container_dependency_bundle $dependencyBundle
                ): application_container_factory => new application_container_factory($factoryBundle, $dependencyBundle)
        );
        $this->containerFactoryFactory = \Closure::fromCallable(
            $containerFactoryFactory
                ?? fn(
                    application_service_factory_bundle $factoryBundle,
                    application_container_dependency_bundle $dependencyBundle
                ): container => ($this->applicationContainerFactoryProvider)($factoryBundle, $dependencyBundle)->create()
        );
    }

    public function __invoke(
        callable $bootstrapOperationsFactory,
        callable $registryDefaultsProviderFactory,
        callable $factoryProviderDefaultsProviderFactory,
        callable $registrarDefaultsProviderFactory,
        callable $creatorDefaultsProviderFactory
    ): callable {
        $containerFactoryFactory = $this->containerFactoryFactory;

        return static function (?context $context = null) use (
            $containerFactoryFactory,
            $bootstrapOperationsFactory,
            $registryDefaultsProviderFactory,
            $factoryProviderDefaultsProviderFactory,
            $registrarDefaultsProviderFactory,
            $creatorDefaultsProviderFactory
        ): container {
            if (!$context instanceof context) {
                throw new \RuntimeException('Application container factory requires a bootstrap context.');
            }
            $factoryOptions = new application_service_factory_options(
                bootstrapOperationsFactory: \Closure::fromCallable($bootstrapOperationsFactory($context))
            );
            $dependencyProvider = new application_container_dependency_provider(
                $registryDefaultsProviderFactory,
                $factoryProviderDefaultsProviderFactory,
                $registrarDefaultsProviderFactory,
                $creatorDefaultsProviderFactory
            );
            $dependencyBundle = application_container_dependency_bundle::fromProvider(
                $dependencyProvider
            );
            $factoryBundle = application_service_factory_bundle::fromProviders(
                $dependencyProvider->applicationRuntimeFactoryProvider(),
                $dependencyProvider->applicationModelFactoryProvider(),
                $dependencyProvider->applicationServiceFactoryRegistry(),
                $factoryOptions
            );

            return $containerFactoryFactory($factoryBundle, $dependencyBundle);
        };
    }
}
