<?php

declare(strict_types=1);

use fan\core\di\application_client_service_registrar;
use fan\core\di\application_content_service_registrar;
use fan\core\di\application_controller_service_registrar;
use fan\core\di\application_core_service_registrar;
use fan\core\di\application_pager_service_registrar;
use fan\core\di\application_infrastructure_service_registrar;
use fan\core\di\application_navigation_service_registrar;
use fan\core\di\application_service_graph_registrar;
use fan\core\di\application_service_registrar_defaults_provider;
use fan\core\di\application_session_service_registrar;
use fan\core\di\application_support_service_registrar;
use fan\core\di\application_user_service_registrar;
use fan\core\di\application_utility_service_registrar;
use PHPUnit\Framework\TestCase;

final class ApplicationServiceRegistrarDefaultsProviderTest extends TestCase
{
    public function testProviderCreatesApplicationServiceRegistrarDefaults(): void
    {
        $defaultsProvider = self::defaultsProvider();

        $this->assertInstanceOf(application_support_service_registrar::class, $defaultsProvider->applicationSupportServiceRegistrar());
        $this->assertInstanceOf(application_service_graph_registrar::class, $defaultsProvider->applicationServiceGraphRegistrar());
        $this->assertInstanceOf(application_core_service_registrar::class, $defaultsProvider->applicationCoreServiceRegistrar());
        $this->assertInstanceOf(application_infrastructure_service_registrar::class, $defaultsProvider->applicationInfrastructureServiceRegistrar());
        $this->assertInstanceOf(application_content_service_registrar::class, $defaultsProvider->applicationContentServiceRegistrar());
        $this->assertInstanceOf(application_navigation_service_registrar::class, $defaultsProvider->applicationNavigationServiceRegistrar());
        $this->assertInstanceOf(application_controller_service_registrar::class, $defaultsProvider->applicationControllerServiceRegistrar());
        $this->assertInstanceOf(application_client_service_registrar::class, $defaultsProvider->applicationClientServiceRegistrar());
        $this->assertInstanceOf(application_pager_service_registrar::class, $defaultsProvider->applicationPagerServiceRegistrar());
        $this->assertInstanceOf(application_utility_service_registrar::class, $defaultsProvider->applicationUtilityServiceRegistrar());
        $this->assertFalse(method_exists($defaultsProvider, 'applicationDatabaseServiceRegistrar'));
        $this->assertFalse(method_exists($defaultsProvider, 'applicationEmailServiceRegistrar'));
        $this->assertInstanceOf(application_session_service_registrar::class, $defaultsProvider->applicationSessionServiceRegistrar());
        $this->assertInstanceOf(application_user_service_registrar::class, $defaultsProvider->applicationUserServiceRegistrar());
    }

    private static function defaultsProvider(): application_service_registrar_defaults_provider
    {
        $coreServiceRegistrar = new application_core_service_registrar();
        $infrastructureServiceRegistrar = new application_infrastructure_service_registrar();
        $contentServiceRegistrar = new application_content_service_registrar();
        $navigationServiceRegistrar = new application_navigation_service_registrar();
        $controllerServiceRegistrar = new application_controller_service_registrar();
        $clientServiceRegistrar = new application_client_service_registrar();
        $pagerServiceRegistrar = new application_pager_service_registrar();
        $utilityServiceRegistrar = new application_utility_service_registrar();
        $sessionServiceRegistrar = new application_session_service_registrar();
        $userServiceRegistrar = new application_user_service_registrar();

        return new application_service_registrar_defaults_provider(
            new application_support_service_registrar(),
            new application_service_graph_registrar(
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
