<?php

declare(strict_types=1);

use fan\core\di\application_client_service_creator;
use fan\core\di\application_content_service_creator;
use fan\core\di\application_controller_service_creator;
use fan\core\di\application_core_service_creator;
use fan\core\di\application_pager_service_creator;
use fan\core\di\application_infrastructure_service_creator;
use fan\core\di\application_navigation_service_creator;
use fan\core\di\application_service_creator_defaults_provider;
use fan\core\di\application_service_creator_defaults_provider_factory;
use fan\core\di\application_session_service_creator;
use fan\core\di\application_user_service_creator;
use fan\core\di\application_utility_service_creator;
use fan\core\di\config_row_factory;
use fan\core\di\date_exception_factory;
use fan\core\di\fatal_exception_factory;
use PHPUnit\Framework\TestCase;

final class ApplicationServiceCreatorDefaultsProviderFactoryTest extends TestCase
{
    public function testFactoryCreatesApplicationServiceCreatorDefaultsProvider(): void
    {
        $provider = (new application_service_creator_defaults_provider_factory())();

        $this->assertInstanceOf(application_service_creator_defaults_provider::class, $provider);
        $this->assertInstanceOf(application_core_service_creator::class, $provider->applicationCoreServiceCreator());
        $this->assertInstanceOf(application_content_service_creator::class, $provider->applicationContentServiceCreator());
        $this->assertInstanceOf(application_navigation_service_creator::class, $provider->applicationNavigationServiceCreator());
        $this->assertInstanceOf(application_controller_service_creator::class, $provider->applicationControllerServiceCreator());
        $this->assertInstanceOf(application_infrastructure_service_creator::class, $provider->applicationInfrastructureServiceCreator());
        $this->assertInstanceOf(application_client_service_creator::class, $provider->applicationClientServiceCreator());
        $this->assertInstanceOf(application_pager_service_creator::class, $provider->applicationPagerServiceCreator());
        $this->assertInstanceOf(application_utility_service_creator::class, $provider->applicationUtilityServiceCreator());
        $this->assertFalse(method_exists($provider, 'applicationDatabaseServiceCreator'));
        $this->assertFalse(method_exists($provider, 'applicationEmailServiceCreator'));
        $this->assertInstanceOf(application_session_service_creator::class, $provider->applicationSessionServiceCreator());
        $this->assertInstanceOf(application_user_service_creator::class, $provider->applicationUserServiceCreator());
    }

    public function testFactoryAcceptsInjectedCreatorDefaults(): void
    {
        $coreCreator = new application_core_service_creator();
        $contentCreator = new application_content_service_creator();
        $navigationCreator = new application_navigation_service_creator();
        $controllerCreator = new application_controller_service_creator();
        $infrastructureCreator = new application_infrastructure_service_creator();
        $clientCreator = new application_client_service_creator();
        $formCreator = new application_pager_service_creator();
        $utilityCreator = new application_utility_service_creator(new date_exception_factory());
        $sessionCreator = new application_session_service_creator(new fatal_exception_factory());
        $userCreator = new application_user_service_creator();

        $provider = (new application_service_creator_defaults_provider_factory(
            static fn(): application_core_service_creator => $coreCreator,
            static fn(): application_content_service_creator => $contentCreator,
            static fn(): application_navigation_service_creator => $navigationCreator,
            static fn(): application_controller_service_creator => $controllerCreator,
            static fn(): application_infrastructure_service_creator => $infrastructureCreator,
            static fn(): application_client_service_creator => $clientCreator,
            static fn(): application_pager_service_creator => $formCreator,
            static fn(): application_utility_service_creator => $utilityCreator,
            static fn(): application_session_service_creator => $sessionCreator,
            static fn(): application_user_service_creator => $userCreator
        ))();

        $this->assertSame($coreCreator, $provider->applicationCoreServiceCreator());
        $this->assertSame($contentCreator, $provider->applicationContentServiceCreator());
        $this->assertSame($navigationCreator, $provider->applicationNavigationServiceCreator());
        $this->assertSame($controllerCreator, $provider->applicationControllerServiceCreator());
        $this->assertSame($infrastructureCreator, $provider->applicationInfrastructureServiceCreator());
        $this->assertSame($clientCreator, $provider->applicationClientServiceCreator());
        $this->assertSame($formCreator, $provider->applicationPagerServiceCreator());
        $this->assertSame($utilityCreator, $provider->applicationUtilityServiceCreator());
        $this->assertSame($sessionCreator, $provider->applicationSessionServiceCreator());
        $this->assertSame($userCreator, $provider->applicationUserServiceCreator());
    }
}
