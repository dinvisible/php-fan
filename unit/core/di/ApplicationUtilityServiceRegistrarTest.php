<?php

declare(strict_types=1);

use fan\core\di\application_utility_service_registrar;
use fan\core\di\application_utility_service_registrar_dependencies;
use PHPUnit\Framework\TestCase;

final class ApplicationUtilityServiceRegistrarTest extends TestCase
{
    public function testUtilityRegistrarOwnsUtilityServiceGraphRegistrations(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_utility_service_registrar.php');
        $graphSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_service_graph_registrar.php');
        $dependencySource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_utility_service_registrar_dependencies.php');
        $dateDependencySource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_utility_date_state_registrar_dependencies.php');
        $obfuscatorDependencySource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_utility_obfuscator_state_registrar_dependencies.php');
        $imageModifyDependencySource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_utility_image_modify_state_registrar_dependencies.php');
        $dependencyProviderSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_container_dependency_provider.php');
        $registrarDefaultsProviderFactorySource = file_get_contents(dirname(__DIR__, 3) . '/core/factory/application_service_registrar_defaults_provider_factory.php');

        $this->assertIsString($source);
        $this->assertIsString($graphSource);
        $this->assertIsString($dependencySource);
        $this->assertIsString($dateDependencySource);
        $this->assertIsString($obfuscatorDependencySource);
        $this->assertIsString($imageModifyDependencySource);
        $this->assertIsString($dependencyProviderSource);
        $this->assertIsString($registrarDefaultsProviderFactorySource);
        $this->assertStringContainsString('final class application_utility_service_registrar', $source);
        $this->assertStringContainsString('final class application_utility_service_registrar_dependencies', $dependencySource);
        $this->assertStringContainsString('final class application_utility_date_state_registrar_dependencies', $dateDependencySource);
        $this->assertStringContainsString('final class application_utility_obfuscator_state_registrar_dependencies', $obfuscatorDependencySource);
        $this->assertStringContainsString('final class application_utility_image_modify_state_registrar_dependencies', $imageModifyDependencySource);
        foreach (['service_id::DATE', 'service_id::OBFUSCATOR', 'service_id::IMAGE_MODIFY', 'service_id::IMAGE_DRAW', 'service_id::SOAP'] as $registration) {
            $this->assertStringContainsString($registration, $source);
        }
        foreach ([
            '$dependenciesFactory($container)->dateState()',
            '$dependenciesFactory($container)->obfuscatorState()',
            '$dependenciesFactory($container)->imageModifyState()',
        ] as $dependencyCall) {
            $this->assertStringContainsString($dependencyCall, $source);
        }
        foreach ([
            'return $this->date->dateState();',
            'return $this->obfuscator->obfuscatorState();',
            'return $this->imageModify->imageModifyState();',
        ] as $dependencyCall) {
            $this->assertStringContainsString($dependencyCall, $dependencySource);
        }
        $this->assertStringContainsString('return $this->container->get(service_id::DATE_STATE);', $dateDependencySource);
        $this->assertStringContainsString('return $this->container->get(service_id::OBFUSCATOR_STATE);', $obfuscatorDependencySource);
        $this->assertStringContainsString('return $this->container->get(service_id::IMAGE_MODIFY_STATE);', $imageModifyDependencySource);
        foreach (['DATE_STATE', 'OBFUSCATOR_STATE', 'IMAGE_MODIFY_STATE'] as $stateConstant) {
            $this->assertStringNotContainsString('->get(service_id::' . $stateConstant . ')', $source);
        }
        foreach ([
            '$utilityServiceCreator->createDateService(',
            '$utilityServiceCreator->createObfuscatorService(',
            '$utilityServiceCreator->createImageModifyService(',
            '$utilityServiceCreator->createSoapService(',
        ] as $creatorCall) {
            $this->assertStringContainsString($creatorCall, $source);
            $this->assertStringNotContainsString($creatorCall, $graphSource);
        }
        $this->assertStringContainsString('private application_utility_service_registrar $utilityServiceRegistrar', $graphSource);
        $this->assertStringContainsString('$this->utilityServiceRegistrar->register(', $graphSource);
        $this->assertStringContainsString('new application_utility_service_registrar()', $registrarDefaultsProviderFactorySource);
        $this->assertStringNotContainsString('new application_utility_service_registrar()', $dependencyProviderSource);
        $this->assertStringNotContainsString("->factory(\n                'date'", $graphSource);
        $this->assertStringNotContainsString("->factory(\n                'obfuscator'", $graphSource);
        $this->assertStringNotContainsString("->factory(\n                'image_modify'", $graphSource);
        $this->assertStringNotContainsString("->factory(\n                'image_draw'", $graphSource);
        $this->assertStringNotContainsString("->factory(\n                'soap'", $graphSource);
    }

    public function testUtilityRegistrarClassIsInstantiable(): void
    {
        $this->assertInstanceOf(application_utility_service_registrar::class, new application_utility_service_registrar());
        $this->assertInstanceOf(
            application_utility_service_registrar_dependencies::class,
            new application_utility_service_registrar_dependencies($this->createStub(\fan\core\di\container_interface::class))
        );
    }
}
