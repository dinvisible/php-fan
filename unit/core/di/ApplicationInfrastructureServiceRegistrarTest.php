<?php

declare(strict_types=1);

use fan\core\di\application_infrastructure_service_registrar;
use PHPUnit\Framework\TestCase;

final class ApplicationInfrastructureServiceRegistrarTest extends TestCase
{
    public function testInfrastructureRegistrarOwnsInfrastructureServiceGraphRegistrations(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_infrastructure_service_registrar.php');
        $graphSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_service_graph_registrar.php');
        $dependencyProviderSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_container_dependency_provider.php');
        $registrarDefaultsProviderFactorySource = file_get_contents(dirname(__DIR__, 3) . '/core/factory/application_service_registrar_defaults_provider_factory.php');

        $this->assertIsString($source);
        $this->assertIsString($graphSource);
        $this->assertIsString($dependencyProviderSource);
        $this->assertIsString($registrarDefaultsProviderFactorySource);
        $this->assertStringContainsString('final class application_infrastructure_service_registrar', $source);
        foreach ([
            'service_id::CONFIG',
            'service_id::CONFIG_CACHE',
            'service_id::CACHE',
            'service_id::JSON',
            'service_id::FILE_SYSTEM',
            'service_id::ELOQUENT',
        ] as $registration) {
            $this->assertStringContainsString($registration, $source);
        }
        $this->assertStringContainsString('private application_infrastructure_service_registrar $infrastructureServiceRegistrar', $graphSource);
        $this->assertStringContainsString('$this->infrastructureServiceRegistrar->register(', $graphSource);
        $this->assertStringContainsString('new application_infrastructure_service_registrar()', $registrarDefaultsProviderFactorySource);
        $this->assertStringNotContainsString('new application_infrastructure_service_registrar()', $dependencyProviderSource);
        $this->assertStringNotContainsString("->factory(\n                'config'", $graphSource);
        $this->assertStringNotContainsString("->factory(\n                'file_system'", $graphSource);
    }

    public function testInfrastructureRegistrarClassIsInstantiable(): void
    {
        $this->assertInstanceOf(application_infrastructure_service_registrar::class, new application_infrastructure_service_registrar());
    }
}
