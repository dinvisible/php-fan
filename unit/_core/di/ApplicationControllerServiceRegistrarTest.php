<?php

declare(strict_types=1);

use fan\core\di\application_controller_service_registrar;
use PHPUnit\Framework\TestCase;

final class ApplicationControllerServiceRegistrarTest extends TestCase
{
    public function testControllerRegistrarOwnsControllerServiceGraphRegistrations(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/di/application_controller_service_registrar.php');
        $graphSource = file_get_contents(dirname(__DIR__, 3) . '/_core/di/application_service_graph_registrar.php');
        $providerSource = file_get_contents(dirname(__DIR__, 3) . '/_core/di/application_service_registrar_defaults_provider.php');
        $dependencyProviderSource = file_get_contents(dirname(__DIR__, 3) . '/_core/di/application_container_dependency_provider.php');
        $registrarDefaultsProviderFactorySource = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/application_service_registrar_defaults_provider_factory.php');

        $this->assertIsString($source);
        $this->assertIsString($graphSource);
        $this->assertIsString($providerSource);
        $this->assertIsString($dependencyProviderSource);
        $this->assertIsString($registrarDefaultsProviderFactorySource);
        $this->assertStringContainsString('final class application_controller_service_registrar', $source);
        foreach (["'plain',"] as $registration) {
            $this->assertStringContainsString($registration, $source);
        }
        $this->assertStringNotContainsString("'cli',", $source);
        $this->assertStringContainsString('private application_controller_service_registrar $controllerServiceRegistrar', $graphSource);
        $this->assertStringContainsString('$this->controllerServiceRegistrar->register(', $graphSource);
        $this->assertStringNotContainsString('new application_controller_service_registrar()', $providerSource);
        $this->assertStringContainsString('new application_controller_service_registrar()', $registrarDefaultsProviderFactorySource);
        $this->assertStringNotContainsString('new application_controller_service_registrar()', $dependencyProviderSource);
        $this->assertStringNotContainsString("->factory(\n                'plain'", $graphSource);
    }

    public function testControllerRegistrarClassIsInstantiable(): void
    {
        $this->assertInstanceOf(application_controller_service_registrar::class, new application_controller_service_registrar());
    }
}
