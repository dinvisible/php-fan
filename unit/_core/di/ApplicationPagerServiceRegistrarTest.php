<?php

declare(strict_types=1);

use fan\core\di\application_pager_service_registrar;
use PHPUnit\Framework\TestCase;

final class ApplicationPagerServiceRegistrarTest extends TestCase
{
    public function testPagerRegistrarOwnsPagerServiceGraphRegistration(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/di/application_pager_service_registrar.php');
        $graphSource = file_get_contents(dirname(__DIR__, 3) . '/_core/di/application_service_graph_registrar.php');
        $dependencyProviderSource = file_get_contents(dirname(__DIR__, 3) . '/_core/di/application_container_dependency_provider.php');
        $registrarDefaultsProviderFactorySource = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/application_service_registrar_defaults_provider_factory.php');

        $this->assertIsString($source);
        $this->assertIsString($graphSource);
        $this->assertIsString($dependencyProviderSource);
        $this->assertIsString($registrarDefaultsProviderFactorySource);
        $this->assertStringContainsString('final class application_pager_service_registrar', $source);
        $this->assertStringContainsString("'pager',", $source);
        $this->assertStringNotContainsString("'form',", $source);
        $this->assertStringContainsString('$pagerServiceCreator->createPagerService(', $source);
        $this->assertStringNotContainsString('createFormService(', $source);
        $this->assertStringContainsString('private application_pager_service_registrar $pagerServiceRegistrar', $graphSource);
        $this->assertStringContainsString('$this->pagerServiceRegistrar->register(', $graphSource);
        $this->assertStringContainsString('new application_pager_service_registrar()', $registrarDefaultsProviderFactorySource);
        $this->assertStringNotContainsString('new application_pager_service_registrar()', $dependencyProviderSource);
        $this->assertStringNotContainsString("->factory(\n                'pager'", $graphSource);
        $this->assertStringNotContainsString("->factory(\n                'form'", $graphSource);
    }

    public function testPagerRegistrarClassIsInstantiable(): void
    {
        $this->assertInstanceOf(application_pager_service_registrar::class, new application_pager_service_registrar());
    }
}
