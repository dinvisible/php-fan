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
use fan\core\di\application_session_service_creator;
use fan\core\di\application_user_service_creator;
use fan\core\di\application_utility_service_creator;
use fan\core\di\date_exception_factory;
use PHPUnit\Framework\TestCase;

final class ApplicationServiceCreatorDefaultsProviderTest extends TestCase
{
    public function testProviderCreatesApplicationServiceCreatorDefaults(): void
    {
        $defaultsProvider = self::defaultsProvider();

        $this->assertInstanceOf(application_core_service_creator::class, $defaultsProvider->applicationCoreServiceCreator());
        $this->assertInstanceOf(application_content_service_creator::class, $defaultsProvider->applicationContentServiceCreator());
        $this->assertInstanceOf(application_navigation_service_creator::class, $defaultsProvider->applicationNavigationServiceCreator());
        $this->assertInstanceOf(application_controller_service_creator::class, $defaultsProvider->applicationControllerServiceCreator());
        $this->assertInstanceOf(application_infrastructure_service_creator::class, $defaultsProvider->applicationInfrastructureServiceCreator());
        $this->assertInstanceOf(application_client_service_creator::class, $defaultsProvider->applicationClientServiceCreator());
        $this->assertInstanceOf(application_pager_service_creator::class, $defaultsProvider->applicationPagerServiceCreator());
        $this->assertInstanceOf(application_utility_service_creator::class, $defaultsProvider->applicationUtilityServiceCreator());
        $this->assertFalse(method_exists($defaultsProvider, 'applicationDatabaseServiceCreator'));
        $this->assertFalse(method_exists($defaultsProvider, 'applicationEmailServiceCreator'));
        $this->assertInstanceOf(application_session_service_creator::class, $defaultsProvider->applicationSessionServiceCreator());
        $this->assertInstanceOf(application_user_service_creator::class, $defaultsProvider->applicationUserServiceCreator());
    }

    private static function defaultsProvider(): application_service_creator_defaults_provider
    {
        return new application_service_creator_defaults_provider(
            new application_core_service_creator(),
            new application_content_service_creator(),
            new application_navigation_service_creator(),
            new application_controller_service_creator(),
            new application_infrastructure_service_creator(),
            new application_client_service_creator(),
            new application_pager_service_creator(),
            new application_utility_service_creator(new date_exception_factory()),
            new application_session_service_creator(),
            new application_user_service_creator()
        );
    }
}
