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
    private ?application_service_registrar_defaults_provider $registrarDefaultsProvider = null;
    private ?application_service_creator_defaults_provider $creatorDefaultsProvider = null;

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
        return $this->registrarDefaultsProvider()->applicationSupportServiceRegistrar();
    }

    public function applicationServiceGraphRegistrar(): application_service_graph_registrar
    {
        return $this->registrarDefaultsProvider()->applicationServiceGraphRegistrar();
    }

    public function applicationCoreServiceRegistrar(): application_core_service_registrar
    {
        return $this->registrarDefaultsProvider()->applicationCoreServiceRegistrar();
    }

    public function applicationInfrastructureServiceRegistrar(): application_infrastructure_service_registrar
    {
        return $this->registrarDefaultsProvider()->applicationInfrastructureServiceRegistrar();
    }

    public function applicationContentServiceRegistrar(): application_content_service_registrar
    {
        return $this->registrarDefaultsProvider()->applicationContentServiceRegistrar();
    }

    public function applicationNavigationServiceRegistrar(): application_navigation_service_registrar
    {
        return $this->registrarDefaultsProvider()->applicationNavigationServiceRegistrar();
    }

    public function applicationControllerServiceRegistrar(): application_controller_service_registrar
    {
        return $this->registrarDefaultsProvider()->applicationControllerServiceRegistrar();
    }

    public function applicationClientServiceRegistrar(): application_client_service_registrar
    {
        return $this->registrarDefaultsProvider()->applicationClientServiceRegistrar();
    }

    public function applicationPagerServiceRegistrar(): application_pager_service_registrar
    {
        return $this->registrarDefaultsProvider()->applicationPagerServiceRegistrar();
    }

    public function applicationUtilityServiceRegistrar(): application_utility_service_registrar
    {
        return $this->registrarDefaultsProvider()->applicationUtilityServiceRegistrar();
    }

    public function applicationSessionServiceRegistrar(): application_session_service_registrar
    {
        return $this->registrarDefaultsProvider()->applicationSessionServiceRegistrar();
    }

    public function applicationUserServiceRegistrar(): application_user_service_registrar
    {
        return $this->registrarDefaultsProvider()->applicationUserServiceRegistrar();
    }

    public function applicationCoreServiceCreator(): application_core_service_creator
    {
        return $this->creatorDefaultsProvider()->applicationCoreServiceCreator();
    }

    public function applicationContentServiceCreator(): application_content_service_creator
    {
        return $this->creatorDefaultsProvider()->applicationContentServiceCreator();
    }

    public function applicationNavigationServiceCreator(): application_navigation_service_creator
    {
        return $this->creatorDefaultsProvider()->applicationNavigationServiceCreator();
    }

    public function applicationControllerServiceCreator(): application_controller_service_creator
    {
        return $this->creatorDefaultsProvider()->applicationControllerServiceCreator();
    }

    public function applicationInfrastructureServiceCreator(): application_infrastructure_service_creator
    {
        return $this->creatorDefaultsProvider()->applicationInfrastructureServiceCreator();
    }

    public function applicationClientServiceCreator(): application_client_service_creator
    {
        return $this->creatorDefaultsProvider()->applicationClientServiceCreator();
    }

    public function applicationPagerServiceCreator(): application_pager_service_creator
    {
        return $this->creatorDefaultsProvider()->applicationPagerServiceCreator();
    }

    public function applicationUtilityServiceCreator(): application_utility_service_creator
    {
        return $this->creatorDefaultsProvider()->applicationUtilityServiceCreator();
    }

    public function applicationSessionServiceCreator(): application_session_service_creator
    {
        return $this->creatorDefaultsProvider()->applicationSessionServiceCreator();
    }

    public function applicationUserServiceCreator(): application_user_service_creator
    {
        return $this->creatorDefaultsProvider()->applicationUserServiceCreator();
    }

    private function registrarDefaultsProvider(): application_service_registrar_defaults_provider
    {
        $factory = $this->registrarDefaultsProviderFactory;

        return $this->registrarDefaultsProvider ??= $factory();
    }

    private function creatorDefaultsProvider(): application_service_creator_defaults_provider
    {
        $factory = $this->creatorDefaultsProviderFactory;

        return $this->creatorDefaultsProvider ??= $factory();
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
}
