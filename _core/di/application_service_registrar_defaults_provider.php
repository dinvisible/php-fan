<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_service_registrar_defaults_provider
{
    public function __construct(
        private application_support_service_registrar $supportServiceRegistrar,
        private application_service_graph_registrar $serviceGraphRegistrar,
        private application_core_service_registrar $coreServiceRegistrar,
        private application_infrastructure_service_registrar $infrastructureServiceRegistrar,
        private application_content_service_registrar $contentServiceRegistrar,
        private application_navigation_service_registrar $navigationServiceRegistrar,
        private application_controller_service_registrar $controllerServiceRegistrar,
        private application_client_service_registrar $clientServiceRegistrar,
        private application_pager_service_registrar $pagerServiceRegistrar,
        private application_utility_service_registrar $utilityServiceRegistrar,
        private application_session_service_registrar $sessionServiceRegistrar,
        private application_user_service_registrar $userServiceRegistrar
    ) {
    }

    public function applicationSupportServiceRegistrar(): application_support_service_registrar
    {
        return $this->supportServiceRegistrar;
    }

    public function applicationServiceGraphRegistrar(): application_service_graph_registrar
    {
        return $this->serviceGraphRegistrar;
    }

    public function applicationCoreServiceRegistrar(): application_core_service_registrar
    {
        return $this->coreServiceRegistrar;
    }

    public function applicationInfrastructureServiceRegistrar(): application_infrastructure_service_registrar
    {
        return $this->infrastructureServiceRegistrar;
    }

    public function applicationContentServiceRegistrar(): application_content_service_registrar
    {
        return $this->contentServiceRegistrar;
    }

    public function applicationNavigationServiceRegistrar(): application_navigation_service_registrar
    {
        return $this->navigationServiceRegistrar;
    }

    public function applicationControllerServiceRegistrar(): application_controller_service_registrar
    {
        return $this->controllerServiceRegistrar;
    }

    public function applicationClientServiceRegistrar(): application_client_service_registrar
    {
        return $this->clientServiceRegistrar;
    }

    public function applicationPagerServiceRegistrar(): application_pager_service_registrar
    {
        return $this->pagerServiceRegistrar;
    }

    public function applicationUtilityServiceRegistrar(): application_utility_service_registrar
    {
        return $this->utilityServiceRegistrar;
    }

    public function applicationSessionServiceRegistrar(): application_session_service_registrar
    {
        return $this->sessionServiceRegistrar;
    }

    public function applicationUserServiceRegistrar(): application_user_service_registrar
    {
        return $this->userServiceRegistrar;
    }
}
