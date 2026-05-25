<?php

declare(strict_types=1);

use fan\core\di\application_utility_service_registrar;
use PHPUnit\Framework\TestCase;

final class ApplicationUtilityServiceRegistrarTest extends TestCase
{
    public function testUtilityRegistrarOwnsUtilityServiceGraphRegistrations(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/di/application_utility_service_registrar.php');
        $graphSource = file_get_contents(dirname(__DIR__, 3) . '/_core/di/application_service_graph_registrar.php');
        $providerSource = file_get_contents(dirname(__DIR__, 3) . '/_core/di/application_service_registrar_defaults_provider.php');
        $dependencyProviderSource = file_get_contents(dirname(__DIR__, 3) . '/_core/di/application_container_dependency_provider.php');
        $registrarDefaultsProviderFactorySource = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/application_service_registrar_defaults_provider_factory.php');

        $this->assertIsString($source);
        $this->assertIsString($graphSource);
        $this->assertIsString($providerSource);
        $this->assertIsString($dependencyProviderSource);
        $this->assertIsString($registrarDefaultsProviderFactorySource);
        $this->assertStringContainsString('final class application_utility_service_registrar', $source);
        foreach (["'date',", "'obfuscator',", "'image_modify',", "'image_draw',", "'soap',"] as $registration) {
            $this->assertStringContainsString($registration, $source);
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
        $this->assertStringNotContainsString('new application_utility_service_registrar()', $providerSource);
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
    }
}
