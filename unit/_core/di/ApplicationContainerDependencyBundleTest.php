<?php

declare(strict_types=1);

use fan\core\di\application_container_dependency_bundle;
use fan\core\di\application_container_dependency_provider;
use fan\core\di\application_core_service_creator;
use fan\core\di\application_deferred_service_factory_provider;
use fan\core\di\application_service_graph_registrar;
use fan\core\di\application_support_service_registrar;
use PHPUnit\Framework\TestCase;
use fan\core\di\application_adapter_registry;
use fan\core\di\application_factory_provider_defaults_provider;
use fan\core\di\application_factory_provider_defaults_provider_factory;
use fan\core\di\application_registry_defaults_provider;
use fan\core\di\application_registry_defaults_provider_factory;
use fan\core\di\application_service_creator_defaults_provider;
use fan\core\di\application_service_creator_defaults_provider_factory;
use fan\core\di\application_service_registrar_defaults_provider;
use fan\core\di\application_service_registrar_defaults_provider_factory;


final class ApplicationContainerDependencyBundleTest extends TestCase
{
    public function testBundleBuildsApplicationContainerDependenciesFromProvider(): void
    {
        $provider = self::dependencyProvider();

        $bundle = application_container_dependency_bundle::fromProvider($provider);

        $this->assertSame($provider, $bundle->dependencyProvider);
        $this->assertInstanceOf(application_core_service_creator::class, $bundle->coreServiceCreator);
        $this->assertInstanceOf(application_support_service_registrar::class, $bundle->supportServiceRegistrar);
        $this->assertInstanceOf(application_service_graph_registrar::class, $bundle->serviceGraphRegistrar);
        $this->assertInstanceOf(application_deferred_service_factory_provider::class, $bundle->deferredServiceFactoryProvider);
    }

    public function testSourceMovesCreatorAndRegistrarPropertiesOutOfContainerFactory(): void
    {
        $bundleSource = file_get_contents(dirname(__DIR__, 3) . '/_core/di/application_container_dependency_bundle.php');
        $containerSource = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/application_container_factory.php');

        $this->assertIsString($bundleSource);
        $this->assertIsString($containerSource);
        $this->assertStringContainsString('final class application_container_dependency_bundle', $bundleSource);
        $this->assertStringContainsString('public application_core_service_creator $coreServiceCreator,', $bundleSource);
        $this->assertStringContainsString('public application_support_service_registrar $supportServiceRegistrar,', $bundleSource);
        $this->assertStringContainsString('public application_service_graph_registrar $serviceGraphRegistrar,', $bundleSource);
        $this->assertStringContainsString('private application_container_dependency_bundle $dependencyBundle;', $containerSource);
        $this->assertStringContainsString('application_container_dependency_bundle $dependencyBundle', $containerSource);
        $this->assertStringNotContainsString('application_container_dependency_bundle::fromProvider(new application_container_dependency_provider())', $containerSource);
        $this->assertStringNotContainsString("require_once __DIR__ . '/application_container_dependency_provider.php';", $containerSource);
        $this->assertStringContainsString('public static function fromProvider(application_container_dependency_provider $dependencyProvider): self', $bundleSource);
        $this->assertStringNotContainsString('new application_container_dependency_provider()', $bundleSource);
        $this->assertStringNotContainsString("require_once __DIR__ . '/application_container_dependency_provider.php';", $bundleSource);
        $this->assertStringNotContainsString('private application_core_service_creator $coreServiceCreator;', $containerSource);
        $this->assertStringNotContainsString('private application_service_graph_registrar $serviceGraphRegistrar;', $containerSource);
        $this->assertStringNotContainsString('?application_core_service_creator $coreServiceCreator = null', $containerSource);
    }

    private static function dependencyProvider(): application_container_dependency_provider
    {
        return new application_container_dependency_provider(
            static fn(): application_registry_defaults_provider => (new application_registry_defaults_provider_factory())(),
            static fn(
                application_adapter_registry $adapterRegistry
            ): application_factory_provider_defaults_provider => (new application_factory_provider_defaults_provider_factory())($adapterRegistry),
            static fn(): application_service_registrar_defaults_provider => (new application_service_registrar_defaults_provider_factory())(),
            static fn(): application_service_creator_defaults_provider => (new application_service_creator_defaults_provider_factory())()
        );
    }
}
