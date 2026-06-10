<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_container_dependency_provider
{
    private \Closure $registryDefaultsProviderFactory;
    private \Closure $factoryProviderDefaultsProviderFactory;
    private \Closure $registrarDefaultsProviderFactory;
    private \Closure $creatorDefaultsProviderFactory;
    private ?application_registry_defaults_provider $registryDefaultsProvider = null;
    private ?application_factory_provider_defaults_provider $factoryProviderDefaultsProvider = null;
    private ?array $registrarDefaults = null;
    private ?array $creatorDefaults = null;

    public function __construct(
        callable $registryDefaultsProviderFactory,
        callable $factoryProviderDefaultsProviderFactory,
        callable $registrarDefaultsProviderFactory,
        callable $creatorDefaultsProviderFactory
    ) {
        $this->registryDefaultsProviderFactory = \Closure::fromCallable($registryDefaultsProviderFactory);
        $this->factoryProviderDefaultsProviderFactory = \Closure::fromCallable($factoryProviderDefaultsProviderFactory);
        $this->registrarDefaultsProviderFactory = \Closure::fromCallable($registrarDefaultsProviderFactory);
        $this->creatorDefaultsProviderFactory = \Closure::fromCallable($creatorDefaultsProviderFactory);
    }

    public function applicationAdapterRegistry(): application_adapter_registry
    {
        return $this->registryDefaultsProvider()->applicationAdapterRegistry();
    }

    public function applicationStateRegistry(): application_state_registry
    {
        return $this->registryDefaultsProvider()->applicationStateRegistry();
    }

    public function applicationModelFactoryProvider(): application_model_factory_provider
    {
        return $this->factoryProviderDefaultsProvider()->applicationModelFactoryProvider();
    }

    public function applicationServiceFactoryRegistry(): service_factory_registry
    {
        return $this->factoryProviderDefaultsProvider()->applicationServiceFactoryRegistry();
    }

    public function applicationRuntimeFactoryProvider(): application_runtime_factory_provider
    {
        return $this->factoryProviderDefaultsProvider()->applicationRuntimeFactoryProvider();
    }

    public function applicationDeferredServiceFactoryProvider(): application_deferred_service_factory_provider
    {
        return $this->factoryProviderDefaultsProvider()->applicationDeferredServiceFactoryProvider();
    }

    public function applicationSupportServiceRegistrar(): application_support_service_registrar
    {
        return $this->registrarDefault('supportServiceRegistrar', application_support_service_registrar::class);
    }

    public function applicationServiceGraphRegistrar(): application_service_graph_registrar
    {
        return $this->registrarDefault('serviceGraphRegistrar', application_service_graph_registrar::class);
    }

    public function applicationCoreServiceRegistrar(): application_core_service_registrar
    {
        return $this->registrarDefault('coreServiceRegistrar', application_core_service_registrar::class);
    }

    public function applicationInfrastructureServiceRegistrar(): application_infrastructure_service_registrar
    {
        return $this->registrarDefault('infrastructureServiceRegistrar', application_infrastructure_service_registrar::class);
    }

    public function applicationContentServiceRegistrar(): application_content_service_registrar
    {
        return $this->registrarDefault('contentServiceRegistrar', application_content_service_registrar::class);
    }

    public function applicationNavigationServiceRegistrar(): application_navigation_service_registrar
    {
        return $this->registrarDefault('navigationServiceRegistrar', application_navigation_service_registrar::class);
    }

    public function applicationControllerServiceRegistrar(): application_controller_service_registrar
    {
        return $this->registrarDefault('controllerServiceRegistrar', application_controller_service_registrar::class);
    }

    public function applicationClientServiceRegistrar(): application_client_service_registrar
    {
        return $this->registrarDefault('clientServiceRegistrar', application_client_service_registrar::class);
    }

    public function applicationPagerServiceRegistrar(): application_pager_service_registrar
    {
        return $this->registrarDefault('pagerServiceRegistrar', application_pager_service_registrar::class);
    }

    public function applicationUtilityServiceRegistrar(): application_utility_service_registrar
    {
        return $this->registrarDefault('utilityServiceRegistrar', application_utility_service_registrar::class);
    }

    public function applicationSessionServiceRegistrar(): application_session_service_registrar
    {
        return $this->registrarDefault('sessionServiceRegistrar', application_session_service_registrar::class);
    }

    public function applicationUserServiceRegistrar(): application_user_service_registrar
    {
        return $this->registrarDefault('userServiceRegistrar', application_user_service_registrar::class);
    }

    public function applicationCoreServiceCreator(): application_core_service_creator
    {
        return $this->creatorDefault('coreServiceCreator', application_core_service_creator::class);
    }

    public function applicationContentServiceCreator(): application_content_service_creator
    {
        return $this->creatorDefault('contentServiceCreator', application_content_service_creator::class);
    }

    public function applicationNavigationServiceCreator(): application_navigation_service_creator
    {
        return $this->creatorDefault('navigationServiceCreator', application_navigation_service_creator::class);
    }

    public function applicationControllerServiceCreator(): application_controller_service_creator
    {
        return $this->creatorDefault('controllerServiceCreator', application_controller_service_creator::class);
    }

    public function applicationInfrastructureServiceCreator(): application_infrastructure_service_creator
    {
        return $this->creatorDefault('infrastructureServiceCreator', application_infrastructure_service_creator::class);
    }

    public function applicationClientServiceCreator(): application_client_service_creator
    {
        return $this->creatorDefault('clientServiceCreator', application_client_service_creator::class);
    }

    public function applicationPagerServiceCreator(): application_pager_service_creator
    {
        return $this->creatorDefault('pagerServiceCreator', application_pager_service_creator::class);
    }

    public function applicationUtilityServiceCreator(): application_utility_service_creator
    {
        return $this->creatorDefault('utilityServiceCreator', application_utility_service_creator::class);
    }

    public function applicationSessionServiceCreator(): application_session_service_creator
    {
        return $this->creatorDefault('sessionServiceCreator', application_session_service_creator::class);
    }

    public function applicationUserServiceCreator(): application_user_service_creator
    {
        return $this->creatorDefault('userServiceCreator', application_user_service_creator::class);
    }

    private function registrarDefault(string $key, string $class): object
    {
        $value = $this->registrarDefaults()[$key] ?? null;
        if (!$value instanceof $class) {
            throw new \UnexpectedValueException('Application registrar default "' . $key . '" must be an instance of ' . $class . '.');
        }

        return $value;
    }

    private function creatorDefault(string $key, string $class): object
    {
        $value = $this->creatorDefaults()[$key] ?? null;
        if (!$value instanceof $class) {
            throw new \UnexpectedValueException('Application creator default "' . $key . '" must be an instance of ' . $class . '.');
        }

        return $value;
    }

    private function registrarDefaults(): array
    {
        $factory = $this->registrarDefaultsProviderFactory;

        return $this->registrarDefaults ??= $this->assertDefaults($factory(), 'registrar');
    }

    private function creatorDefaults(): array
    {
        $factory = $this->creatorDefaultsProviderFactory;

        return $this->creatorDefaults ??= $this->assertDefaults($factory(), 'creator');
    }

    private function registryDefaultsProvider(): application_registry_defaults_provider
    {
        $factory = $this->registryDefaultsProviderFactory;

        return $this->registryDefaultsProvider ??= $factory();
    }

    private function factoryProviderDefaultsProvider(): application_factory_provider_defaults_provider
    {
        $factory = $this->factoryProviderDefaultsProviderFactory;

        return $this->factoryProviderDefaultsProvider ??= $factory(
            $this->applicationAdapterRegistry()
        );
    }

    private function assertDefaults(mixed $defaults, string $type): array
    {
        if (!is_array($defaults)) {
            throw new \UnexpectedValueException('Application ' . $type . ' defaults factory must return an array.');
        }

        return $defaults;
    }
}
