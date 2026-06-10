<?php

declare(strict_types=1);

use fan\core\di\application_adapter_registry;
use fan\core\di\application_adapter_registry_defaults_provider;
use fan\core\di\application_container_dependency_provider;
use fan\core\di\application_client_service_factory_defaults_provider;
use fan\core\di\application_content_service_factory_defaults_provider;
use fan\core\di\application_core_service_factory_defaults_provider;
use fan\core\di\application_pager_service_factory_defaults_provider;
use fan\core\di\application_infrastructure_service_factory_defaults_provider;
use fan\core\di\application_navigation_service_factory_defaults_provider;
use fan\core\di\application_service_engine_factory_defaults_provider;
use fan\core\di\application_service_factory_defaults_provider;
use fan\core\di\service_factory_registry;
use fan\core\di\application_session_service_factory_defaults_provider;
use fan\core\di\application_service_sub_factory_defaults_provider;
use fan\core\di\application_user_service_factory_defaults_provider;
use fan\core\di\application_utility_service_factory_defaults_provider;
use fan\core\di\block_exception_factory;
use fan\core\di\block_factory;
use fan\core\di\cache_engine_factory;
use fan\core\di\cache_service_factory;
use fan\core\di\application_service_factory;
use fan\core\di\config_service_factory;
use fan\core\di\cookie_service_factory;
use fan\core\di\curl_service_factory;
use fan\core\di\date_service_factory;
use fan\core\di\debug_service_factory;
use fan\core\di\delayed_meta_factory;
use fan\core\di\error_service_factory;
use fan\core\di\file_system_service_factory;
use fan\core\di\header_service_factory;
use fan\core\di\image_modify_service_factory;
use fan\core\di\json_service_factory;
use fan\core\di\locale_service_factory;
use fan\core\di\matcher_item_component_factory;
use fan\core\di\matcher_item_factory;
use fan\core\di\matcher_service_factory;
use fan\core\di\meta_maker_factory;
use fan\core\di\obfuscator_service_factory;
use fan\core\di\pager_service_factory;
use fan\core\di\plain_service_factory;
use fan\core\di\plain_controller_factory;
use fan\core\di\reflector_service_factory;
use fan\core\di\request_service_factory;
use fan\core\di\rest_service_factory;
use fan\core\di\role_service_factory;
use fan\core\di\service_engine_factory;
use fan\core\di\session_engine_factory;
use fan\core\di\session_service_factory;
use fan\core\di\soap_service_factory;
use fan\core\di\tab_delegate_factory;
use fan\core\di\tab_service_factory;
use fan\core\di\tab_view_parser_factory;
use fan\core\di\translation_service_factory;
use fan\core\di\timer_program_factory;
use fan\core\di\timer_service_factory;
use fan\core\di\user_engine_factory;
use fan\core\di\user_service_factory;
use fan\core\di\view_definer_factory;
use fan\core\di\view_keeper_factory;
use fan\core\di\view_loader_json_keeper_factory;
use fan\core\di\view_loader_state_factory;
use fan\core\di\view_loader_text_keeper_factory;
use fan\core\di\view_router_factory;
use PHPUnit\Framework\TestCase;
use fan\core\adapter\reflection_class_factory;
use fan\core\base\meta\maker_state;


final class ApplicationServiceFactoryDefaultsProviderTest extends TestCase
{
    public function testProviderCreatesApplicationServiceFactoryRegistryWithDefaultFactories(): void
    {
        $defaultsProvider = self::defaultsProvider();

        $serviceFactoryRegistry = $defaultsProvider->applicationServiceFactoryRegistry();

        $this->assertInstanceOf(service_factory_registry::class, $serviceFactoryRegistry);
        $this->assertIsCallable(($serviceFactoryRegistry->get('bootstrapRuntimeServiceFactory'))());
        $this->assertIsCallable(($serviceFactoryRegistry->get('serviceEngineFactory'))(self::configuredServiceFactory()));
        $this->assertIsCallable(($serviceFactoryRegistry->get('configServiceFactory'))(self::configuredServiceFactory()));
        $this->assertIsCallable(($serviceFactoryRegistry->get('matcherItemFactory'))());
        $this->assertFalse(method_exists($serviceFactoryRegistry, 'emailEngineFactory'));
        $this->assertFalse(method_exists($serviceFactoryRegistry, 'emailServiceFactory'));
        $this->assertFalse(method_exists($serviceFactoryRegistry, 'databaseEngineFactory'));
    }

    private static function configuredServiceFactory(): callable
    {
        return static fn(string $className, array $arguments = []): object => new stdClass();
    }

    private static function defaultsProvider(): application_service_factory_defaults_provider
    {
        return new application_service_factory_defaults_provider(
            new application_service_engine_factory_defaults_provider(
                static fn(): callable => static fn(): object => new stdClass(),
                static fn(callable $configuredServiceFactory): callable => new service_engine_factory($configuredServiceFactory),
                static fn(
                object $serializerOperations,
                callable $configuredServiceFactory,
                object $cacheFileStorage
            ): callable => new cache_engine_factory(
                $serializerOperations,
                $configuredServiceFactory,
                $cacheFileStorage,
                null,
                null,
                new reflection_class_factory()
            ),
                static fn(callable $configuredServiceFactory, object $nativeSession): callable => new session_engine_factory(
                    $configuredServiceFactory,
                    $nativeSession,
                    null,
                    new reflection_class_factory()
                ),
                static fn(object $serializerOperations, callable $configuredServiceFactory): callable => new user_engine_factory(
                    $serializerOperations,
                    $configuredServiceFactory
                )
            ),
            new application_service_sub_factory_defaults_provider(
                static fn(): callable => new matcher_item_factory(),
                static fn(callable $configuredServiceFactory): callable => new matcher_item_component_factory($configuredServiceFactory),
                static fn(callable $configuredServiceFactory): callable => new tab_delegate_factory($configuredServiceFactory),
                static fn(callable $configuredServiceFactory): callable => new tab_view_parser_factory($configuredServiceFactory),
                static fn(callable $configuredServiceFactory): callable => new plain_controller_factory($configuredServiceFactory),
                static fn(callable $configuredServiceFactory): callable => new block_factory($configuredServiceFactory),
                static fn(callable $configuredServiceFactory): callable => new block_exception_factory($configuredServiceFactory)
            ),
            new application_core_service_factory_defaults_provider(
                static fn(callable $configuredServiceFactory): callable => new request_service_factory(
                    $configuredServiceFactory,
                    new reflection_class_factory()
                ),
                static fn(callable $configuredServiceFactory): callable => new role_service_factory(
                    $configuredServiceFactory,
                    new reflection_class_factory()
                ),
                static fn(callable $configuredServiceFactory): callable => new error_service_factory(
                    $configuredServiceFactory,
                    new reflection_class_factory()
                ),
                static fn(callable $configuredServiceFactory): callable => new reflector_service_factory($configuredServiceFactory),
                static fn(callable $configuredServiceFactory): callable => new application_service_factory(
                    $configuredServiceFactory,
                    new reflection_class_factory()
                ),
                static fn(callable $configuredServiceFactory): callable => new debug_service_factory($configuredServiceFactory),
                static fn(callable $configuredServiceFactory): callable => new header_service_factory(
                    $configuredServiceFactory,
                    null,
                    new reflection_class_factory()
                ),
                static fn(callable $configuredServiceFactory): callable => new matcher_service_factory(
                    $configuredServiceFactory,
                    new reflection_class_factory()
                ),
                static fn(callable $configuredServiceFactory): callable => new timer_service_factory(
                    $configuredServiceFactory,
                    new reflection_class_factory()
                ),
                static fn(callable $configuredServiceFactory): callable => new plain_service_factory(
                    $configuredServiceFactory,
                    new reflection_class_factory()
                ),
                static fn(callable $configuredServiceFactory): callable => new locale_service_factory(
                    $configuredServiceFactory,
                    new reflection_class_factory()
                ),
                static fn(callable $configuredServiceFactory): callable => new timer_program_factory($configuredServiceFactory)
            ),
            new application_client_service_factory_defaults_provider(
                static fn(callable $configuredServiceFactory): callable => new curl_service_factory(
                    $configuredServiceFactory,
                    new reflection_class_factory()
                ),
                static fn(callable $configuredServiceFactory): callable => new rest_service_factory(
                    $configuredServiceFactory,
                    new reflection_class_factory()
                ),
                static fn(callable $configuredServiceFactory): callable => new cookie_service_factory(
                    $configuredServiceFactory,
                    new reflection_class_factory()
                )
            ),
            new application_infrastructure_service_factory_defaults_provider(
                static fn(callable $configuredServiceFactory): callable => new config_service_factory(
                    $configuredServiceFactory,
                    null,
                    new reflection_class_factory()
                ),
                static fn(callable $configuredServiceFactory): callable => new file_system_service_factory(
                    $configuredServiceFactory,
                    new reflection_class_factory()
                ),
                static fn(callable $configuredServiceFactory): callable => new json_service_factory(
                    $configuredServiceFactory,
                    new reflection_class_factory()
                ),
                static fn(callable $configuredServiceFactory): callable => new cache_service_factory(
                    $configuredServiceFactory,
                    new reflection_class_factory()
                )
            ),
            new application_pager_service_factory_defaults_provider(
                static fn(callable $configuredServiceFactory): callable => new pager_service_factory(
                    $configuredServiceFactory,
                    new reflection_class_factory()
                )
            ),
            new application_utility_service_factory_defaults_provider(
                static fn(callable $configuredServiceFactory): callable => new obfuscator_service_factory(
                    $configuredServiceFactory,
                    new reflection_class_factory()
                ),
                static fn(callable $configuredServiceFactory): callable => new image_modify_service_factory(
                    $configuredServiceFactory,
                    new reflection_class_factory()
                ),
                static fn(callable $configuredServiceFactory): callable => new soap_service_factory(
                    $configuredServiceFactory,
                    new reflection_class_factory()
                ),
                static fn(callable $configuredServiceFactory): callable => new date_service_factory(
                    $configuredServiceFactory,
                    new reflection_class_factory()
                )
            ),
            new application_navigation_service_factory_defaults_provider(
                static fn(callable $configuredServiceFactory): callable => new tab_service_factory(
                    $configuredServiceFactory,
                    new view_definer_factory(),
                    new meta_maker_factory(new delayed_meta_factory()),
                    new view_router_factory(new view_keeper_factory()),
                    new view_loader_state_factory(
                        new view_loader_json_keeper_factory(),
                        new view_loader_text_keeper_factory()
                    ),
                    static fn(): object => new maker_state(),
                    new reflection_class_factory()
                )
            ),
            new application_content_service_factory_defaults_provider(
                static fn(callable $configuredServiceFactory): callable => new translation_service_factory(
                    $configuredServiceFactory,
                    new reflection_class_factory()
                )
            ),
            new application_session_service_factory_defaults_provider(
                static fn(callable $configuredServiceFactory): callable => new session_service_factory(
                    $configuredServiceFactory,
                    new reflection_class_factory()
                )
            ),
            new application_user_service_factory_defaults_provider(
                static fn(callable $configuredServiceFactory): callable => new user_service_factory(
                    $configuredServiceFactory,
                    new reflection_class_factory()
                )
            ),
            static fn(array $factories): service_factory_registry => new service_factory_registry($factories)
        );
    }
}
