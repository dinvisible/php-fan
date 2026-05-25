<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_service_creator_defaults_provider
{
    public function __construct(
        private application_core_service_creator $coreServiceCreator,
        private application_content_service_creator $contentServiceCreator,
        private application_navigation_service_creator $navigationServiceCreator,
        private application_controller_service_creator $controllerServiceCreator,
        private application_infrastructure_service_creator $infrastructureServiceCreator,
        private application_client_service_creator $clientServiceCreator,
        private application_pager_service_creator $pagerServiceCreator,
        private application_utility_service_creator $utilityServiceCreator,
        private application_session_service_creator $sessionServiceCreator,
        private application_user_service_creator $userServiceCreator
    ) {
    }

    public function applicationCoreServiceCreator(): application_core_service_creator
    {
        return $this->coreServiceCreator;
    }

    public function applicationContentServiceCreator(): application_content_service_creator
    {
        return $this->contentServiceCreator;
    }

    public function applicationNavigationServiceCreator(): application_navigation_service_creator
    {
        return $this->navigationServiceCreator;
    }

    public function applicationControllerServiceCreator(): application_controller_service_creator
    {
        return $this->controllerServiceCreator;
    }

    public function applicationInfrastructureServiceCreator(): application_infrastructure_service_creator
    {
        return $this->infrastructureServiceCreator;
    }

    public function applicationClientServiceCreator(): application_client_service_creator
    {
        return $this->clientServiceCreator;
    }

    public function applicationPagerServiceCreator(): application_pager_service_creator
    {
        return $this->pagerServiceCreator;
    }

    public function applicationUtilityServiceCreator(): application_utility_service_creator
    {
        return $this->utilityServiceCreator;
    }

    public function applicationSessionServiceCreator(): application_session_service_creator
    {
        return $this->sessionServiceCreator;
    }

    public function applicationUserServiceCreator(): application_user_service_creator
    {
        return $this->userServiceCreator;
    }
}
