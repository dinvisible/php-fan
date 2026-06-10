<?php

declare(strict_types=1);

use fan\core\di\application_core_service_registrar;
use PHPUnit\Framework\TestCase;

final class ApplicationCoreServiceRegistrarTest extends TestCase
{
    public function testCoreRegistrarOwnsCoreServiceGraphRegistrations(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_service_registrar.php');
        $graphSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_service_graph_registrar.php');
        $dependencyProviderSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_container_dependency_provider.php');
        $registrarDefaultsProviderFactorySource = file_get_contents(dirname(__DIR__, 3) . '/core/factory/application_service_registrar_defaults_provider_factory.php');

        $this->assertIsString($source);
        $this->assertIsString($graphSource);
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
