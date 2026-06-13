<?php

declare(strict_types=1);

use fan\core\di\application_user_service_registrar;
use fan\core\di\application_user_service_registrar_dependencies;
use PHPUnit\Framework\TestCase;

final class ApplicationUserServiceRegistrarTest extends TestCase
{
    public function testUserRegistrarOwnsUserServiceGraphRegistrations(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_user_service_registrar.php');
        $graphSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_service_graph_registrar.php');
        $dependencySource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_user_service_registrar_dependencies.php');
        $userStateDependencySource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_user_state_registrar_dependencies.php');
        $dependencyProviderSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_container_dependency_provider.php');
        $registrarDefaultsProviderFactorySource = file_get_contents(dirname(__DIR__, 3) . '/core/factory/application_service_registrar_defaults_provider_factory.php');

        $this->assertIsString($source);
        $this->assertIsString($graphSource);
        $this->assertIsString($dependencySource);
        $this->assertIsString($userStateDependencySource);
        $this->assertIsString($dependencyProviderSource);
        $this->assertIsString($registrarDefaultsProviderFactorySource);
        $this->assertStringContainsString('final class application_user_service_registrar', $source);
        $this->assertStringContainsString('final class application_user_service_registrar_dependencies', $dependencySource);
        $this->assertStringContainsString('final class application_user_state_registrar_dependencies', $userStateDependencySource);
        foreach (['service_id::USER', 'service_id::CURRENT_USER', 'service_id::CURRENT_USER_CHECKED', 'service_id::CURRENT_USER_SPACE'] as $registration) {
            $this->assertStringContainsString($registration, $source);
        }
        foreach ([
            '$dependencies->userState()',
            '$dependencies->bootstrapRuntime()',
            '$dependencies->config()',
            '$dependencies->cacheFactory()',
            '$dependenciesFactory($container)->userState()',
        ] as $dependencyCall) {
            $this->assertStringContainsString($dependencyCall, $source);
        }
        foreach (['USER_STATE', 'BOOTSTRAP_RUNTIME', 'CONFIG', 'CACHE'] as $serviceConstant) {
            $this->assertStringNotContainsString('->get(service_id::' . $serviceConstant, $source);
        }
        $this->assertStringContainsString('return $this->container->get(service_id::USER_STATE);', $userStateDependencySource);
        foreach ([
            '$userServiceCreator->createUserService(',
            '$userServiceCreator->getCurrentUserService(',
            '$userServiceCreator->getCurrentUserServiceChecked(',
            '$userServiceCreator->getCurrentUserSpace(',
        ] as $creatorCall) {
            $this->assertStringContainsString($creatorCall, $source);
            $this->assertStringNotContainsString($creatorCall, $graphSource);
        }
        $this->assertStringContainsString('private application_user_service_registrar $userServiceRegistrar', $graphSource);
        $this->assertStringContainsString('$this->userServiceRegistrar->register(', $graphSource);
        $this->assertStringContainsString('new application_user_service_registrar()', $registrarDefaultsProviderFactorySource);
        $this->assertStringNotContainsString('new application_user_service_registrar()', $dependencyProviderSource);
        $this->assertStringNotContainsString("->factory(\n                'user'", $graphSource);
        $this->assertStringNotContainsString("->factory('current_user'", $graphSource);
    }

    public function testUserRegistrarClassIsInstantiable(): void
    {
        $this->assertInstanceOf(application_user_service_registrar::class, new application_user_service_registrar());
        $this->assertInstanceOf(
            application_user_service_registrar_dependencies::class,
            new application_user_service_registrar_dependencies($this->createStub(\fan\core\di\container_interface::class))
        );
    }
}
