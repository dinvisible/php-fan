<?php

declare(strict_types=1);

use fan\core\di\application_core_service_registrar;
use PHPUnit\Framework\TestCase;

final class ApplicationCoreServiceRegistrarTest extends TestCase
{
    public function testCoreRegistrarOwnsCoreServiceGraphRegistrations(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/di/application_core_service_registrar.php');
        $graphSource = file_get_contents(dirname(__DIR__, 3) . '/_core/di/application_service_graph_registrar.php');
        $providerSource = file_get_contents(dirname(__DIR__, 3) . '/_core/di/application_service_registrar_defaults_provider.php');
        $dependencyProviderSource = file_get_contents(dirname(__DIR__, 3) . '/_core/di/application_container_dependency_provider.php');
        $registrarDefaultsProviderFactorySource = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/application_service_registrar_defaults_provider_factory.php');

        $this->assertIsString($source);
        $this->assertIsString($graphSource);
        $this->assertIsString($providerSource);
        $this->assertIsString($dependencyProviderSource);
        $this->assertIsString($registrarDefaultsProviderFactorySource);
        $this->assertStringContainsString('final class application_core_service_registrar', $source);
        foreach ([
            "'request',",
            "'role',",
            "'error',",
            "'header',",
            "'locale',",
            "'application',",
            "'matcher',",
            "'reflector',",
            "'debug',",
            "'timer',",
        ] as $registration) {
            $this->assertStringContainsString($registration, $source);
        }
        $this->assertStringContainsString('private application_core_service_registrar $coreServiceRegistrar', $graphSource);
        $this->assertStringContainsString('$this->coreServiceRegistrar->register($container, $context)', $graphSource);
        $this->assertStringContainsString('private application_core_service_registrar $coreServiceRegistrar,', $providerSource);
        $this->assertStringContainsString('private application_infrastructure_service_registrar $infrastructureServiceRegistrar,', $providerSource);
        $this->assertStringContainsString('return $this->coreServiceRegistrar;', $providerSource);
        $this->assertStringNotContainsString('$this->applicationCoreServiceRegistrar()', $providerSource);
        $this->assertStringNotContainsString('$this->applicationInfrastructureServiceRegistrar()', $providerSource);
        $this->assertStringContainsString('new application_core_service_registrar()', $registrarDefaultsProviderFactorySource);
        $this->assertStringNotContainsString('new application_core_service_registrar()', $dependencyProviderSource);
        $this->assertStringNotContainsString("->factory(\n                'request'", $graphSource);
        $this->assertStringNotContainsString("->factory(\n                'timer'", $graphSource);
    }

    public function testCoreRegistrarClassIsInstantiable(): void
    {
        $this->assertInstanceOf(application_core_service_registrar::class, new application_core_service_registrar());
    }
}
