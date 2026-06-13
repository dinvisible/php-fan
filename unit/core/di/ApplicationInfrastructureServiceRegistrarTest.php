<?php

declare(strict_types=1);

use fan\core\di\application_infrastructure_service_registrar;
use fan\core\di\application_infrastructure_service_registrar_dependencies;
use PHPUnit\Framework\TestCase;

final class ApplicationInfrastructureServiceRegistrarTest extends TestCase
{
    public function testInfrastructureRegistrarOwnsInfrastructureServiceGraphRegistrations(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_infrastructure_service_registrar.php');
        $graphSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_service_graph_registrar.php');
        $dependencySource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_infrastructure_service_registrar_dependencies.php');
        $configStateDependencySource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_infrastructure_config_state_registrar_dependencies.php');
        $dependencyProviderSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_container_dependency_provider.php');
        $registrarDefaultsProviderFactorySource = file_get_contents(dirname(__DIR__, 3) . '/core/factory/application_service_registrar_defaults_provider_factory.php');

        $this->assertIsString($source);
        $this->assertIsString($graphSource);
        $this->assertIsString($dependencySource);
        $this->assertIsString($configStateDependencySource);
        $this->assertIsString($dependencyProviderSource);
        $this->assertIsString($registrarDefaultsProviderFactorySource);
        $this->assertStringContainsString('final class application_infrastructure_service_registrar', $source);
        $this->assertStringContainsString('final class application_infrastructure_service_registrar_dependencies', $dependencySource);
        $this->assertStringContainsString('final class application_infrastructure_config_state_registrar_dependencies', $configStateDependencySource);
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
        foreach ([
            '$dependencies->configState()',
            '$dependencies->bootstrapRuntime()',
            '$dependencies->cacheFactory()',
            '$dependencies->cacheState()',
            '$dependencies->cacheMemcacheState()',
            '$dependenciesFactory($container)->jsonState()',
            '$dependencies->fileSystemState()',
            '$dependencies->config()',
            '$dependenciesFactory($container)->config()',
        ] as $dependencyCall) {
            $this->assertStringContainsString($dependencyCall, $source);
        }
        foreach (['CONFIG_STATE', 'BOOTSTRAP_RUNTIME', 'CACHE', 'CACHE_STATE', 'CACHE_MEMCACHE_STATE', 'JSON_STATE', 'FILE_SYSTEM_STATE', 'CONFIG'] as $serviceConstant) {
            $this->assertStringNotContainsString('->get(service_id::' . $serviceConstant, $source);
        }
        $this->assertStringContainsString('return $this->container->get(service_id::CONFIG_STATE);', $configStateDependencySource);
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
        $this->assertInstanceOf(
            application_infrastructure_service_registrar_dependencies::class,
            new application_infrastructure_service_registrar_dependencies($this->createStub(\fan\core\di\container_interface::class))
        );
    }
}
