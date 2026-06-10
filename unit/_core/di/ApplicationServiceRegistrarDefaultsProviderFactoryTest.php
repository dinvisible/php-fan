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
use fan\core\di\application_service_registrar_defaults_provider_factory;
use fan\core\di\application_session_service_registrar;
use fan\core\di\application_support_service_registrar;
use fan\core\di\application_user_service_registrar;
use fan\core\di\application_utility_service_registrar;
use fan\core\di\plain_exception_factory;
use fan\core\di\transfer_exception_factory;
use fan\core\di\view_router_factory;
use PHPUnit\Framework\TestCase;

final class ApplicationServiceRegistrarDefaultsProviderFactoryTest extends TestCase
{
    public function testFactoryCreatesApplicationServiceRegistrarDefaultsProvider(): void
    {
        $defaults = (new application_service_registrar_defaults_provider_factory())();

        $this->assertIsArray($defaults);
        $this->assertTrue(class_exists(plain_exception_factory::class));
        $this->assertInstanceOf(application_support_service_registrar::class, $defaults['supportServiceRegistrar']);
        $this->assertInstanceOf(application_service_graph_registrar::class, $defaults['serviceGraphRegistrar']);
        $this->assertInstanceOf(application_core_service_registrar::class, $defaults['coreServiceRegistrar']);
        $this->assertInstanceOf(application_infrastructure_service_registrar::class, $defaults['infrastructureServiceRegistrar']);
        $this->assertInstanceOf(application_content_service_registrar::class, $defaults['contentServiceRegistrar']);
        $this->assertInstanceOf(application_navigation_service_registrar::class, $defaults['navigationServiceRegistrar']);
        $this->assertInstanceOf(application_controller_service_registrar::class, $defaults['controllerServiceRegistrar']);
        $this->assertInstanceOf(application_client_service_registrar::class, $defaults['clientServiceRegistrar']);
        $this->assertInstanceOf(application_pager_service_registrar::class, $defaults['pagerServiceRegistrar']);
        $this->assertInstanceOf(application_utility_service_registrar::class, $defaults['utilityServiceRegistrar']);
        $this->assertArrayNotHasKey('databaseServiceRegistrar', $defaults);
        $this->assertArrayNotHasKey('emailServiceRegistrar', $defaults);
        $this->assertInstanceOf(application_session_service_registrar::class, $defaults['sessionServiceRegistrar']);
        $this->assertInstanceOf(application_user_service_registrar::class, $defaults['userServiceRegistrar']);
    }

    public function testFactoryAcceptsInjectedRegistrarDefaults(): void
    {
        $supportRegistrar = new application_support_service_registrar();
        $coreRegistrar = new application_core_service_registrar();
        $infrastructureRegistrar = new application_infrastructure_service_registrar();
        $contentRegistrar = new application_content_service_registrar();
        $navigationRegistrar = new application_navigation_service_registrar();
        $controllerRegistrar = new application_controller_service_registrar();
        $clientRegistrar = new application_client_service_registrar();
        $formRegistrar = new application_pager_service_registrar();
        $utilityRegistrar = new application_utility_service_registrar();
        $sessionRegistrar = new application_session_service_registrar();
        $userRegistrar = new application_user_service_registrar();
        $graphRegistrar = null;

        $defaults = (new application_service_registrar_defaults_provider_factory(
            static fn(): application_support_service_registrar => $supportRegistrar,
            function (
                application_core_service_registrar $core,
                application_infrastructure_service_registrar $infrastructure,
                application_content_service_registrar $content,
                application_navigation_service_registrar $navigation,
                application_controller_service_registrar $controller,
                application_client_service_registrar $client,
                application_pager_service_registrar $form,
                application_utility_service_registrar $utility,
                application_session_service_registrar $session,
                application_user_service_registrar $user
            ) use (
                &$graphRegistrar,
                $coreRegistrar,
                $infrastructureRegistrar,
                $contentRegistrar,
                $navigationRegistrar,
                $controllerRegistrar,
                $clientRegistrar,
                $formRegistrar,
                $utilityRegistrar,
                $sessionRegistrar,
                $userRegistrar
            ): application_service_graph_registrar {
                $this->assertSame($coreRegistrar, $core);
                $this->assertSame($infrastructureRegistrar, $infrastructure);
                $this->assertSame($contentRegistrar, $content);
                $this->assertSame($navigationRegistrar, $navigation);
                $this->assertSame($controllerRegistrar, $controller);
                $this->assertSame($clientRegistrar, $client);
                $this->assertSame($formRegistrar, $form);
                $this->assertSame($utilityRegistrar, $utility);
                $this->assertSame($sessionRegistrar, $session);
                $this->assertSame($userRegistrar, $user);

                return $graphRegistrar = new application_service_graph_registrar(
                    $core,
                    $infrastructure,
                    $content,
                    $navigation,
                    $controller,
                    $client,
                    $form,
                    $utility,
                    $session,
                    $user
                );
            },
            static fn(): application_core_service_registrar => $coreRegistrar,
            static fn(): application_infrastructure_service_registrar => $infrastructureRegistrar,
            static fn(): application_content_service_registrar => $contentRegistrar,
            static fn(): application_navigation_service_registrar => $navigationRegistrar,
            static fn(): application_controller_service_registrar => $controllerRegistrar,
            static fn(): application_client_service_registrar => $clientRegistrar,
            static fn(): application_pager_service_registrar => $formRegistrar,
            static fn(): application_utility_service_registrar => $utilityRegistrar,
            static fn(): application_session_service_registrar => $sessionRegistrar,
            static fn(): application_user_service_registrar => $userRegistrar
        ))();

        $this->assertSame($supportRegistrar, $defaults['supportServiceRegistrar']);
        $this->assertSame($graphRegistrar, $defaults['serviceGraphRegistrar']);
        $this->assertSame($coreRegistrar, $defaults['coreServiceRegistrar']);
        $this->assertSame($infrastructureRegistrar, $defaults['infrastructureServiceRegistrar']);
        $this->assertSame($contentRegistrar, $defaults['contentServiceRegistrar']);
        $this->assertSame($navigationRegistrar, $defaults['navigationServiceRegistrar']);
        $this->assertSame($controllerRegistrar, $defaults['controllerServiceRegistrar']);
        $this->assertSame($clientRegistrar, $defaults['clientServiceRegistrar']);
        $this->assertSame($formRegistrar, $defaults['pagerServiceRegistrar']);
        $this->assertSame($utilityRegistrar, $defaults['utilityServiceRegistrar']);
        $this->assertSame($sessionRegistrar, $defaults['sessionServiceRegistrar']);
        $this->assertSame($userRegistrar, $defaults['userServiceRegistrar']);
    }
}
