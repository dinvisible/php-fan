<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_service_graph_registrar
{
    public function __construct(
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

    public function register(
        container $container,
        application_service_graph_registration_context $context
    ): void {
        $this->userServiceRegistrar->register(
            $this->sessionServiceRegistrar->register(
                $this->utilityServiceRegistrar->register(
                    $this->pagerServiceRegistrar->register(
                        $this->clientServiceRegistrar->register(
                            $this->controllerServiceRegistrar->register(
                                $this->navigationServiceRegistrar->register(
                                    $this->contentServiceRegistrar->register(
                                        $this->infrastructureServiceRegistrar->register(
                                            $this->coreServiceRegistrar->register($container, $context),
                                            $context
                                        ),
                                        $context
                                    ),
                                    $context
                                ),
                                $context
                            ),
                            $context
                        ),
                        $context
                    ),
                    $context
                ),
                $context
            ),
            $context
        );
    }
}
