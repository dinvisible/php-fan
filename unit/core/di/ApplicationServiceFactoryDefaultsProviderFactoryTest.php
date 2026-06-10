<?php

declare(strict_types=1);

use fan\core\di\application_registry_defaults_provider_factory;
use fan\core\di\application_client_service_factory_defaults_provider;
use fan\core\di\application_content_service_factory_defaults_provider;
use fan\core\di\application_core_service_factory_defaults_provider;
use fan\core\di\application_pager_service_factory_defaults_provider;
use fan\core\di\application_infrastructure_service_factory_defaults_provider;
use fan\core\di\application_navigation_service_factory_defaults_provider;
use fan\core\di\application_service_engine_factory_defaults_provider;
use fan\core\di\application_service_factory_defaults_provider;
use fan\core\di\application_service_factory_defaults_provider_factory;
use fan\core\di\service_factory_registry;
use fan\core\di\application_service_sub_factory_defaults_provider;
use fan\core\di\application_session_service_factory_defaults_provider;
use fan\core\di\application_user_service_factory_defaults_provider;
use fan\core\di\application_utility_service_factory_defaults_provider;
use PHPUnit\Framework\TestCase;

final class ApplicationServiceFactoryDefaultsProviderFactoryTest extends TestCase
{
    public function testFactoryCreatesApplicationServiceFactoryDefaultsProvider(): void
    {
        $adapterRegistry = (new application_registry_defaults_provider_factory())()->applicationAdapterRegistry();
        $defaultsProvider = (new application_service_factory_defaults_provider_factory())($adapterRegistry);

        $this->assertInstanceOf(application_service_factory_defaults_provider::class, $defaultsProvider);
        $this->assertInstanceOf(service_factory_registry::class, $defaultsProvider->applicationServiceFactoryRegistry());
    }

    public function testFactoryAcceptsInjectedServiceFactoryDefaultProviders(): void
    {
        $adapterRegistry = (new application_registry_defaults_provider_factory())()->applicationAdapterRegistry();
        $serviceFactoryRegistryFactoryCallCount = 0;
        $defaultsProvider = (new application_service_factory_defaults_provider_factory(
            static fn(): application_service_engine_factory_defaults_provider => new application_service_engine_factory_defaults_provider(
                static fn(): callable => static fn(): object => (object)['name' => 'bootstrap-runtime'],
                self::configuredFactoryMarker('service-engine'),
                self::configuredFactoryMarker('cache-engine'),
                self::configuredFactoryMarker('session-engine'),
                self::configuredFactoryMarker('user-engine')
            ),
            static fn(): application_service_sub_factory_defaults_provider => new application_service_sub_factory_defaults_provider(
                static fn(): callable => static fn(): object => (object)['name' => 'matcher-item'],
                self::configuredFactoryMarker('matcher-item-component'),
                self::configuredFactoryMarker('tab-delegate'),
                self::configuredFactoryMarker('tab-view-parser'),
                self::configuredFactoryMarker('plain-controller'),
                self::configuredFactoryMarker('block'),
                self::configuredFactoryMarker('block-exception')
            ),
            static fn(): application_core_service_factory_defaults_provider => new application_core_service_factory_defaults_provider(
                self::configuredFactoryMarker('request'),
                self::configuredFactoryMarker('role'),
                self::configuredFactoryMarker('error'),
                self::configuredFactoryMarker('reflector'),
                self::configuredFactoryMarker('application'),
                self::configuredFactoryMarker('debug'),
                self::configuredFactoryMarker('header'),
                self::configuredFactoryMarker('matcher'),
                self::configuredFactoryMarker('timer'),
                self::configuredFactoryMarker('plain'),
                self::configuredFactoryMarker('locale'),
                self::configuredFactoryMarker('timer-program')
            ),
            static fn(): application_client_service_factory_defaults_provider => new application_client_service_factory_defaults_provider(
                self::configuredFactoryMarker('curl'),
                self::configuredFactoryMarker('rest'),
                self::configuredFactoryMarker('cookie')
            ),
            static fn(): application_infrastructure_service_factory_defaults_provider => new application_infrastructure_service_factory_defaults_provider(
                self::configuredFactoryMarker('config'),
                self::configuredFactoryMarker('file-system'),
                self::configuredFactoryMarker('json'),
                self::configuredFactoryMarker('cache')
            ),
            static fn(): application_pager_service_factory_defaults_provider => new application_pager_service_factory_defaults_provider(
                self::configuredFactoryMarker('pager')
            ),
            static fn(): application_utility_service_factory_defaults_provider => new application_utility_service_factory_defaults_provider(
                self::configuredFactoryMarker('obfuscator'),
                self::configuredFactoryMarker('image-modify'),
                self::configuredFactoryMarker('soap'),
                self::configuredFactoryMarker('date')
            ),
            static fn(): application_navigation_service_factory_defaults_provider => new application_navigation_service_factory_defaults_provider(
                self::configuredFactoryMarker('tab')
            ),
            static fn(): application_content_service_factory_defaults_provider => new application_content_service_factory_defaults_provider(
                self::configuredFactoryMarker('translation')
            ),
            static fn(): application_session_service_factory_defaults_provider => new application_session_service_factory_defaults_provider(
                self::configuredFactoryMarker('session')
            ),
            static fn(): application_user_service_factory_defaults_provider => new application_user_service_factory_defaults_provider(
                self::configuredFactoryMarker('user')
            ),
            function (array $factories) use (&$serviceFactoryRegistryFactoryCallCount): service_factory_registry {
                ++$serviceFactoryRegistryFactoryCallCount;

                return new service_factory_registry($factories);
            }
        ))($adapterRegistry);

        $serviceFactoryRegistry = $defaultsProvider->applicationServiceFactoryRegistry();

        $this->assertSame(1, $serviceFactoryRegistryFactoryCallCount);
        $this->assertSame('bootstrap-runtime', ($serviceFactoryRegistry->get('bootstrapRuntimeServiceFactory'))()()->name);
        $this->assertSame('service-engine', ($serviceFactoryRegistry->get('serviceEngineFactory'))(self::configuredServiceFactory())()->name);
        $this->assertSame('matcher-item', ($serviceFactoryRegistry->get('matcherItemFactory'))()()->name);
        $this->assertSame('request', ($serviceFactoryRegistry->get('requestServiceFactory'))(self::configuredServiceFactory())()->name);
        $this->assertSame('curl', ($serviceFactoryRegistry->get('curlServiceFactory'))(self::configuredServiceFactory())()->name);
        $this->assertSame('config', ($serviceFactoryRegistry->get('configServiceFactory'))(self::configuredServiceFactory())()->name);
        $this->assertSame('pager', ($serviceFactoryRegistry->get('pagerServiceFactory'))(self::configuredServiceFactory())()->name);
        $this->assertSame('obfuscator', ($serviceFactoryRegistry->get('obfuscatorServiceFactory'))(self::configuredServiceFactory())()->name);
        $this->assertSame('tab', ($serviceFactoryRegistry->get('tabServiceFactory'))(self::configuredServiceFactory())()->name);
        $this->assertSame('translation', ($serviceFactoryRegistry->get('translationServiceFactory'))(self::configuredServiceFactory())()->name);
        $this->assertSame('session', ($serviceFactoryRegistry->get('sessionServiceFactory'))(self::configuredServiceFactory())()->name);
        $this->assertFalse(method_exists($serviceFactoryRegistry, 'databaseServiceFactory'));
        $this->assertFalse(method_exists($serviceFactoryRegistry, 'emailServiceFactory'));
        $this->assertSame('user', ($serviceFactoryRegistry->get('userServiceFactory'))(self::configuredServiceFactory())()->name);
    }
    private static function configuredFactoryMarker(string $name): callable
    {
        return static fn(callable $configuredServiceFactory): callable => static fn(): object => (object)['name' => $name];
    }

    private static function configuredServiceFactory(): callable
    {
        return static fn(string $className, array $arguments = []): object => new stdClass();
    }
}
