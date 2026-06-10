<?php

declare(strict_types=1);

use fan\core\di\application_client_service_registrar;
use PHPUnit\Framework\TestCase;

final class ApplicationClientServiceRegistrarTest extends TestCase
{
    public function testClientRegistrarOwnsClientServiceGraphRegistrations(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/di/application_client_service_registrar.php');
        $graphSource = file_get_contents(dirname(__DIR__, 3) . '/_core/di/application_service_graph_registrar.php');
        $dependencyProviderSource = file_get_contents(dirname(__DIR__, 3) . '/_core/di/application_container_dependency_provider.php');
        $registrarDefaultsProviderFactorySource = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/application_service_registrar_defaults_provider_factory.php');

        $this->assertIsString($source);
        $this->assertIsString($graphSource);
        $this->assertIsString($dependencyProviderSource);
        $this->assertIsString($registrarDefaultsProviderFactorySource);
        $this->assertStringContainsString('final class application_client_service_registrar', $source);
        foreach (["'cookie',", "'curl',", "'rest',"] as $registration) {
            $this->assertStringContainsString($registration, $source);
        }
        foreach ([
            '$clientServiceCreator->createCookieService(',
            '$clientServiceCreator->createCurlService(',
            '$clientServiceCreator->createRestService(',
        ] as $creatorCall) {
            $this->assertStringContainsString($creatorCall, $source);
            $this->assertStringNotContainsString($creatorCall, $graphSource);
        }
        $this->assertStringContainsString('private application_client_service_registrar $clientServiceRegistrar', $graphSource);
        $this->assertStringContainsString('$this->clientServiceRegistrar->register(', $graphSource);
        $this->assertStringContainsString('new application_client_service_registrar()', $registrarDefaultsProviderFactorySource);
        $this->assertStringNotContainsString('new application_client_service_registrar()', $dependencyProviderSource);
        $this->assertStringNotContainsString("->factory(\n                'cookie'", $graphSource);
        $this->assertStringNotContainsString("->factory(\n                'curl'", $graphSource);
        $this->assertStringNotContainsString("->factory(\n                'rest'", $graphSource);
    }

    public function testClientRegistrarClassIsInstantiable(): void
    {
        $this->assertInstanceOf(application_client_service_registrar::class, new application_client_service_registrar());
    }
}
