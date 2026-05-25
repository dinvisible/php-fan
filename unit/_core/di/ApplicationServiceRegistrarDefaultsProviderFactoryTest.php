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
        $provider = (new application_service_registrar_defaults_provider_factory())();

        $this->assertInstanceOf(application_service_registrar_defaults_provider::class, $provider);
        $this->assertTrue(class_exists(plain_exception_factory::class));
        $this->assertInstanceOf(application_support_service_registrar::class, $provider->applicationSupportServiceRegistrar());
        $this->assertInstanceOf(application_service_graph_registrar::class, $provider->applicationServiceGraphRegistrar());
        $this->assertInstanceOf(application_core_service_registrar::class, $provider->applicationCoreServiceRegistrar());
        $this->assertInstanceOf(application_infrastructure_service_registrar::class, $provider->applicationInfrastructureServiceRegistrar());
        $this->assertInstanceOf(application_content_service_registrar::class, $provider->applicationContentServiceRegistrar());
        $this->assertInstanceOf(application_navigation_service_registrar::class, $provider->applicationNavigationServiceRegistrar());
        $this->assertInstanceOf(application_controller_service_registrar::class, $provider->applicationControllerServiceRegistrar());
        $this->assertInstanceOf(application_client_service_registrar::class, $provider->applicationClientServiceRegistrar());
        $this->assertInstanceOf(application_pager_service_registrar::class, $provider->applicationPagerServiceRegistrar());
        $this->assertInstanceOf(application_utility_service_registrar::class, $provider->applicationUtilityServiceRegistrar());
        $this->assertFalse(method_exists($provider, 'applicationDatabaseServiceRegistrar'));
        $this->assertFalse(method_exists($provider, 'applicationEmailServiceRegistrar'));
        $this->assertInstanceOf(application_session_service_registrar::class, $provider->applicationSessionServiceRegistrar());
        $this->assertInstanceOf(application_user_service_registrar::class, $provider->applicationUserServiceRegistrar());
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

        $provider = (new application_service_registrar_defaults_provider_factory(
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

        $this->assertSame($supportRegistrar, $provider->applicationSupportServiceRegistrar());
        $this->assertSame($graphRegistrar, $provider->applicationServiceGraphRegistrar());
        $this->assertSame($coreRegistrar, $provider->applicationCoreServiceRegistrar());
        $this->assertSame($infrastructureRegistrar, $provider->applicationInfrastructureServiceRegistrar());
        $this->assertSame($contentRegistrar, $provider->applicationContentServiceRegistrar());
        $this->assertSame($navigationRegistrar, $provider->applicationNavigationServiceRegistrar());
        $this->assertSame($controllerRegistrar, $provider->applicationControllerServiceRegistrar());
        $this->assertSame($clientRegistrar, $provider->applicationClientServiceRegistrar());
        $this->assertSame($formRegistrar, $provider->applicationPagerServiceRegistrar());
        $this->assertSame($utilityRegistrar, $provider->applicationUtilityServiceRegistrar());
        $this->assertSame($sessionRegistrar, $provider->applicationSessionServiceRegistrar());
        $this->assertSame($userRegistrar, $provider->applicationUserServiceRegistrar());
    }
}
