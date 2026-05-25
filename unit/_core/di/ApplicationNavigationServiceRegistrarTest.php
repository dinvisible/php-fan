<?php

declare(strict_types=1);

use fan\core\di\application_navigation_service_registrar;
use PHPUnit\Framework\TestCase;

final class ApplicationNavigationServiceRegistrarTest extends TestCase
{
    public function testNavigationRegistrarOwnsNavigationServiceGraphRegistrations(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/di/application_navigation_service_registrar.php');
        $graphSource = file_get_contents(dirname(__DIR__, 3) . '/_core/di/application_service_graph_registrar.php');
        $providerSource = file_get_contents(dirname(__DIR__, 3) . '/_core/di/application_service_registrar_defaults_provider.php');
        $dependencyProviderSource = file_get_contents(dirname(__DIR__, 3) . '/_core/di/application_container_dependency_provider.php');
        $registrarDefaultsProviderFactorySource = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/application_service_registrar_defaults_provider_factory.php');

        $this->assertIsString($source);
        $this->assertIsString($graphSource);
        $this->assertIsString($providerSource);
        $this->assertIsString($dependencyProviderSource);
        $this->assertIsString($registrarDefaultsProviderFactorySource);
        $this->assertStringContainsString('final class application_navigation_service_registrar', $source);
        $this->assertStringContainsString("'tab',", $source);
        $this->assertStringContainsString('private application_navigation_service_registrar $navigationServiceRegistrar', $graphSource);
        $this->assertStringContainsString('$this->navigationServiceRegistrar->register(', $graphSource);
        $this->assertStringNotContainsString('new application_navigation_service_registrar()', $providerSource);
        $this->assertStringContainsString('new application_navigation_service_registrar()', $registrarDefaultsProviderFactorySource);
        $this->assertStringNotContainsString('new application_navigation_service_registrar()', $dependencyProviderSource);
        $this->assertStringNotContainsString("->factory(\n                'tab'", $graphSource);
    }

    public function testNavigationRegistrarClassIsInstantiable(): void
    {
        $this->assertInstanceOf(application_navigation_service_registrar::class, new application_navigation_service_registrar());
    }
}
