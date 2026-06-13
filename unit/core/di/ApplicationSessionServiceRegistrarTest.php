<?php

declare(strict_types=1);

use fan\core\di\application_session_service_registrar;
use fan\core\di\application_session_service_registrar_dependencies;
use PHPUnit\Framework\TestCase;

final class ApplicationSessionServiceRegistrarTest extends TestCase
{
    public function testSessionRegistrarOwnsSessionServiceGraphRegistration(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_session_service_registrar.php');
        $graphSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_service_graph_registrar.php');
        $dependencySource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_session_service_registrar_dependencies.php');
        $dependencyProviderSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_container_dependency_provider.php');
        $registrarDefaultsProviderFactorySource = file_get_contents(dirname(__DIR__, 3) . '/core/factory/application_service_registrar_defaults_provider_factory.php');

        $this->assertIsString($source);
        $this->assertIsString($graphSource);
        $this->assertIsString($dependencySource);
        $this->assertIsString($dependencyProviderSource);
        $this->assertIsString($registrarDefaultsProviderFactorySource);
        $this->assertStringContainsString('final class application_session_service_registrar', $source);
        $this->assertStringContainsString('final class application_session_service_registrar_dependencies', $dependencySource);
        $this->assertStringContainsString('service_id::SESSION', $source);
        $this->assertStringContainsString('$sessionServiceCreator->createSessionService(', $source);
        $this->assertStringContainsString('$dependenciesFactory($container)->sessionState()', $source);
        $this->assertStringNotContainsString('->get(service_id::SESSION_STATE)', $source);
        $this->assertStringContainsString('return $this->container->get(service_id::SESSION_STATE);', $dependencySource);
        $this->assertStringNotContainsString('$sessionServiceCreator->createSessionService(', $graphSource);
        $this->assertStringContainsString('private application_session_service_registrar $sessionServiceRegistrar', $graphSource);
        $this->assertStringContainsString('$this->sessionServiceRegistrar->register(', $graphSource);
        $this->assertStringContainsString('new application_session_service_registrar()', $registrarDefaultsProviderFactorySource);
        $this->assertStringNotContainsString('new application_session_service_registrar()', $dependencyProviderSource);
        $this->assertStringNotContainsString("->factory(\n                'session'", $graphSource);
    }

    public function testSessionRegistrarClassIsInstantiable(): void
    {
        $this->assertInstanceOf(application_session_service_registrar::class, new application_session_service_registrar());
        $this->assertInstanceOf(
            application_session_service_registrar_dependencies::class,
            new application_session_service_registrar_dependencies($this->createStub(\fan\core\di\container_interface::class))
        );
    }
}
