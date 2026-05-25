<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_service_registrar_defaults_provider_factory
{
    private \Closure $supportServiceRegistrarFactory;
    private \Closure $serviceGraphRegistrarFactory;
    private \Closure $coreServiceRegistrarFactory;
    private \Closure $infrastructureServiceRegistrarFactory;
    private \Closure $contentServiceRegistrarFactory;
    private \Closure $navigationServiceRegistrarFactory;
    private \Closure $controllerServiceRegistrarFactory;
    private \Closure $clientServiceRegistrarFactory;
    private \Closure $pagerServiceRegistrarFactory;
    private \Closure $utilityServiceRegistrarFactory;
    private \Closure $sessionServiceRegistrarFactory;
    private \Closure $userServiceRegistrarFactory;

    public function __construct(
        ?callable $supportServiceRegistrarFactory = null,
        ?callable $serviceGraphRegistrarFactory = null,
        ?callable $coreServiceRegistrarFactory = null,
        ?callable $infrastructureServiceRegistrarFactory = null,
        ?callable $contentServiceRegistrarFactory = null,
        ?callable $navigationServiceRegistrarFactory = null,
        ?callable $controllerServiceRegistrarFactory = null,
        ?callable $clientServiceRegistrarFactory = null,
        ?callable $pagerServiceRegistrarFactory = null,
        ?callable $utilityServiceRegistrarFactory = null,
        ?callable $sessionServiceRegistrarFactory = null,
        ?callable $userServiceRegistrarFactory = null
    )
    {
        $this->supportServiceRegistrarFactory = \Closure::fromCallable(
            $supportServiceRegistrarFactory
                ?? static fn(): application_support_service_registrar => new application_support_service_registrar()
        );
        $this->serviceGraphRegistrarFactory = \Closure::fromCallable(
            $serviceGraphRegistrarFactory
                ?? static fn(
                    application_core_service_registrar $coreServiceRegistrar,
                    application_infrastructure_service_registrar $infrastructureServiceRegistrar,
                    application_content_service_registrar $contentServiceRegistrar,
                    application_navigation_service_registrar $navigationServiceRegistrar,
                    application_controller_service_registrar $controllerServiceRegistrar,
                    application_client_service_registrar $clientServiceRegistrar,
                    application_pager_service_registrar $pagerServiceRegistrar,
                    application_utility_service_registrar $utilityServiceRegistrar,
                    application_session_service_registrar $sessionServiceRegistrar,
                    application_user_service_registrar $userServiceRegistrar
                ): application_service_graph_registrar => new application_service_graph_registrar(
                    $coreServiceRegistrar,
                    $infrastructureServiceRegistrar,
                    $contentServiceRegistrar,
                    $navigationServiceRegistrar,
                    $controllerServiceRegistrar,
                    $clientServiceRegistrar,
                    $pagerServiceRegistrar,
                    $utilityServiceRegistrar,
                    $sessionServiceRegistrar,
                    $userServiceRegistrar
                )
        );
        $this->coreServiceRegistrarFactory = \Closure::fromCallable(
            $coreServiceRegistrarFactory
                ?? static fn(): application_core_service_registrar => new application_core_service_registrar()
        );
        $this->infrastructureServiceRegistrarFactory = \Closure::fromCallable(
            $infrastructureServiceRegistrarFactory
                ?? static fn(): application_infrastructure_service_registrar => new application_infrastructure_service_registrar()
        );
        $this->contentServiceRegistrarFactory = \Closure::fromCallable(
            $contentServiceRegistrarFactory
                ?? static fn(): application_content_service_registrar => new application_content_service_registrar()
        );
        $this->navigationServiceRegistrarFactory = \Closure::fromCallable(
            $navigationServiceRegistrarFactory
                ?? static fn(): application_navigation_service_registrar => new application_navigation_service_registrar()
        );
        $this->controllerServiceRegistrarFactory = \Closure::fromCallable(
            $controllerServiceRegistrarFactory
                ?? static fn(): application_controller_service_registrar => new application_controller_service_registrar()
        );
        $this->clientServiceRegistrarFactory = \Closure::fromCallable(
            $clientServiceRegistrarFactory
                ?? static fn(): application_client_service_registrar => new application_client_service_registrar()
        );
        $this->pagerServiceRegistrarFactory = \Closure::fromCallable(
            $pagerServiceRegistrarFactory
                ?? static fn(): application_pager_service_registrar => new application_pager_service_registrar()
        );
        $this->utilityServiceRegistrarFactory = \Closure::fromCallable(
            $utilityServiceRegistrarFactory
                ?? static fn(): application_utility_service_registrar => new application_utility_service_registrar()
        );
        $this->sessionServiceRegistrarFactory = \Closure::fromCallable(
            $sessionServiceRegistrarFactory
                ?? static fn(): application_session_service_registrar => new application_session_service_registrar()
        );
        $this->userServiceRegistrarFactory = \Closure::fromCallable(
            $userServiceRegistrarFactory
                ?? static fn(): application_user_service_registrar => new application_user_service_registrar()
        );
    }

    public function __invoke(): application_service_registrar_defaults_provider
    {
        $coreServiceRegistrar = ($this->coreServiceRegistrarFactory)();
        $infrastructureServiceRegistrar = ($this->infrastructureServiceRegistrarFactory)();
        $contentServiceRegistrar = ($this->contentServiceRegistrarFactory)();
        $navigationServiceRegistrar = ($this->navigationServiceRegistrarFactory)();
        $controllerServiceRegistrar = ($this->controllerServiceRegistrarFactory)();
        $clientServiceRegistrar = ($this->clientServiceRegistrarFactory)();
        $pagerServiceRegistrar = ($this->pagerServiceRegistrarFactory)();
        $utilityServiceRegistrar = ($this->utilityServiceRegistrarFactory)();
        $sessionServiceRegistrar = ($this->sessionServiceRegistrarFactory)();
        $userServiceRegistrar = ($this->userServiceRegistrarFactory)();

        return new application_service_registrar_defaults_provider(
            ($this->supportServiceRegistrarFactory)(),
            ($this->serviceGraphRegistrarFactory)(
                $coreServiceRegistrar,
                $infrastructureServiceRegistrar,
                $contentServiceRegistrar,
                $navigationServiceRegistrar,
                $controllerServiceRegistrar,
                $clientServiceRegistrar,
                $pagerServiceRegistrar,
                $utilityServiceRegistrar,
                $sessionServiceRegistrar,
                $userServiceRegistrar
            ),
            $coreServiceRegistrar,
            $infrastructureServiceRegistrar,
            $contentServiceRegistrar,
            $navigationServiceRegistrar,
            $controllerServiceRegistrar,
            $clientServiceRegistrar,
            $pagerServiceRegistrar,
            $utilityServiceRegistrar,
            $sessionServiceRegistrar,
            $userServiceRegistrar
        );
    }
}
