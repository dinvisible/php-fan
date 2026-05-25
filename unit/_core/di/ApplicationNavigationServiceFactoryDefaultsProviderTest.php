<?php

declare(strict_types=1);

use fan\core\di\application_navigation_service_factory_defaults_provider;
use fan\core\di\delayed_meta_factory;
use fan\core\di\meta_maker_factory;
use fan\core\di\tab_service_factory;
use fan\core\di\view_definer_factory;
use fan\core\di\view_keeper_factory;
use fan\core\di\view_loader_json_keeper_factory;
use fan\core\di\view_loader_state_factory;
use fan\core\di\view_loader_text_keeper_factory;
use fan\core\di\view_router_factory;
use PHPUnit\Framework\TestCase;
use fan\core\adapter\reflection_class_factory;
use fan\core\base\meta\maker_state;


final class ApplicationNavigationServiceFactoryDefaultsProviderTest extends TestCase
{
    public function testProviderCreatesNavigationFactoryDefaults(): void
    {
        $defaultsProvider = new application_navigation_service_factory_defaults_provider(
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
        );

        $this->assertIsCallable($defaultsProvider->tabServiceFactory()(self::configuredServiceFactory()));
    }

    private static function configuredServiceFactory(): callable
    {
        return static fn(string $className, array $arguments = []): object => new stdClass();
    }
}
