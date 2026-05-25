<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_container_dependency_bundle
{
    public function __construct(
        public application_container_dependency_provider $dependencyProvider,
        public application_core_service_creator $coreServiceCreator,
        public application_content_service_creator $contentServiceCreator,
        public application_navigation_service_creator $navigationServiceCreator,
        public application_controller_service_creator $controllerServiceCreator,
        public application_infrastructure_service_creator $infrastructureServiceCreator,
        public application_client_service_creator $clientServiceCreator,
        public application_pager_service_creator $pagerServiceCreator,
        public application_utility_service_creator $utilityServiceCreator,
        public application_session_service_creator $sessionServiceCreator,
        public application_user_service_creator $userServiceCreator,
        public application_support_service_registrar $supportServiceRegistrar,
        public application_service_graph_registrar $serviceGraphRegistrar,
        public application_deferred_service_factory_provider $deferredServiceFactoryProvider
    ) {
    }

    public static function fromProvider(application_container_dependency_provider $dependencyProvider): self
    {
        return new self(
            $dependencyProvider,
            $dependencyProvider->applicationCoreServiceCreator(),
            $dependencyProvider->applicationContentServiceCreator(),
            $dependencyProvider->applicationNavigationServiceCreator(),
            $dependencyProvider->applicationControllerServiceCreator(),
            $dependencyProvider->applicationInfrastructureServiceCreator(),
            $dependencyProvider->applicationClientServiceCreator(),
            $dependencyProvider->applicationPagerServiceCreator(),
            $dependencyProvider->applicationUtilityServiceCreator(),
            $dependencyProvider->applicationSessionServiceCreator(),
            $dependencyProvider->applicationUserServiceCreator(),
            $dependencyProvider->applicationSupportServiceRegistrar(),
            $dependencyProvider->applicationServiceGraphRegistrar(),
            $dependencyProvider->applicationDeferredServiceFactoryProvider()
        );
    }
}
