<?php

declare(strict_types=1);

use fan\core\di\application_core_service_registrar;
use fan\core\di\application_content_service_registrar;
use fan\core\di\application_controller_service_registrar;
use fan\core\di\application_client_service_registrar;
use fan\core\di\application_pager_service_registrar;
use fan\core\di\application_infrastructure_service_registrar;
use fan\core\di\application_navigation_service_registrar;
use fan\core\di\application_service_graph_registrar;
use fan\core\di\application_service_graph_registration_context;
use fan\core\di\application_session_service_registrar;
use fan\core\di\application_user_service_registrar;
use fan\core\di\application_utility_service_registrar;
use PHPUnit\Framework\TestCase;

final class ApplicationServiceGraphRegistrarTest extends TestCase
{
    public function testRegistrarOwnsApplicationServiceGraphRegistrations(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_service_graph_registrar.php');
        $coreRegistrarSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_core_service_registrar.php');
        $infrastructureRegistrarSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_infrastructure_service_registrar.php');
        $contentRegistrarSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_content_service_registrar.php');
        $navigationRegistrarSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_navigation_service_registrar.php');
        $controllerRegistrarSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_controller_service_registrar.php');
        $clientRegistrarSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_client_service_registrar.php');
        $formRegistrarSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_pager_service_registrar.php');
        $utilityRegistrarSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_utility_service_registrar.php');
        $sessionRegistrarSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_session_service_registrar.php');
        $userRegistrarSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_user_service_registrar.php');
        $contextSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_service_graph_registration_context.php');
        $containerSource = file_get_contents(dirname(__DIR__, 3) . '/core/factory/application_container_factory.php');
        $bundleSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_container_dependency_bundle.php');

        $this->assertIsString($source);
        $this->assertIsString($coreRegistrarSource);
        $this->assertIsString($infrastructureRegistrarSource);
        $this->assertIsString($contentRegistrarSource);
        $this->assertIsString($navigationRegistrarSource);
        $this->assertIsString($controllerRegistrarSource);
        $this->assertIsString($clientRegistrarSource);
        $this->assertIsString($formRegistrarSource);
        $this->assertIsString($utilityRegistrarSource);
        $this->assertIsString($sessionRegistrarSource);
        $this->assertIsString($userRegistrarSource);
        $this->assertIsString($contextSource);
        $this->assertIsString($containerSource);
        $this->assertIsString($bundleSource);
        $this->assertStringContainsString('final class application_service_graph_registrar', $source);
        $this->assertStringContainsString('final class application_core_service_registrar', $coreRegistrarSource);
        $this->assertStringContainsString('final class application_infrastructure_service_registrar', $infrastructureRegistrarSource);
        $this->assertStringContainsString('final class application_content_service_registrar', $contentRegistrarSource);
        $this->assertStringContainsString('final class application_navigation_service_registrar', $navigationRegistrarSource);
        $this->assertStringContainsString('final class application_controller_service_registrar', $controllerRegistrarSource);
        $this->assertStringContainsString('final class application_client_service_registrar', $clientRegistrarSource);
        $this->assertStringContainsString('final class application_pager_service_registrar', $formRegistrarSource);
        $this->assertStringContainsString('final class application_utility_service_registrar', $utilityRegistrarSource);
        $this->assertStringContainsString('final class application_session_service_registrar', $sessionRegistrarSource);
        $this->assertStringContainsString('final class application_user_service_registrar', $userRegistrarSource);
        $this->assertStringContainsString('private application_core_service_registrar $coreServiceRegistrar', $source);
        $this->assertStringContainsString('private application_infrastructure_service_registrar $infrastructureServiceRegistrar', $source);
        $this->assertStringContainsString('private application_content_service_registrar $contentServiceRegistrar', $source);
        $this->assertStringContainsString('private application_navigation_service_registrar $navigationServiceRegistrar', $source);
        $this->assertStringContainsString('private application_controller_service_registrar $controllerServiceRegistrar', $source);
        $this->assertStringContainsString('private application_client_service_registrar $clientServiceRegistrar', $source);
        $this->assertStringContainsString('private application_pager_service_registrar $pagerServiceRegistrar', $source);
        $this->assertStringContainsString('private application_utility_service_registrar $utilityServiceRegistrar', $source);
        $this->assertStringNotContainsString('application_database_service_registrar', $source);
        $this->assertStringNotContainsString('application_email_service_registrar', $source);
        $this->assertStringContainsString('private application_session_service_registrar $sessionServiceRegistrar', $source);
        $this->assertStringContainsString('private application_user_service_registrar $userServiceRegistrar', $source);
        $this->assertStringContainsString('$this->coreServiceRegistrar->register($container, $context)', $source);
        $this->assertStringContainsString('$this->infrastructureServiceRegistrar->register(', $source);
        $this->assertStringContainsString('$this->contentServiceRegistrar->register(', $source);
        $this->assertStringContainsString('$this->navigationServiceRegistrar->register(', $source);
        $this->assertStringContainsString('$this->controllerServiceRegistrar->register(', $source);
        $this->assertStringContainsString('$this->clientServiceRegistrar->register(', $source);
        $this->assertStringContainsString('$this->pagerServiceRegistrar->register(', $source);
        $this->assertStringContainsString('$this->utilityServiceRegistrar->register(', $source);
        $this->assertStringNotContainsString('$this->databaseServiceRegistrar->register(', $source);
        $this->assertStringContainsString('$this->sessionServiceRegistrar->register(', $source);
        $this->assertStringNotContainsString('$this->emailServiceRegistrar->register(', $source);
        $this->assertStringContainsString('$this->userServiceRegistrar->register(', $source);
        $this->assertStringContainsString('final class application_service_graph_registration_context', $contextSource);
        $this->assertStringContainsString('application_service_graph_registration_context $context', $source);
        foreach (["'request',", "'role',", "'timer',"] as $registration) {
            $this->assertStringContainsString($registration, $coreRegistrarSource);
        }
        foreach (["'config',", "'cache'", "'json',", "'file_system',"] as $registration) {
            $this->assertStringContainsString($registration, $infrastructureRegistrarSource);
        }
        foreach (["'translation',", "'block_factory'"] as $registration) {
            $this->assertStringContainsString($registration, $contentRegistrarSource);
        }
        $this->assertStringNotContainsString("'template',", $contentRegistrarSource);
        $this->assertStringContainsString("'tab',", $navigationRegistrarSource);
        foreach (["'plain',"] as $registration) {
            $this->assertStringContainsString($registration, $controllerRegistrarSource);
        }
        $this->assertStringNotContainsString("'cli',", $controllerRegistrarSource);
        foreach (["'cookie',", "'curl',", "'rest',"] as $registration) {
            $this->assertStringContainsString($registration, $clientRegistrarSource);
        }
        foreach (["'pager',"] as $registration) {
            $this->assertStringContainsString($registration, $formRegistrarSource);
        }
        $this->assertStringNotContainsString("'form',", $formRegistrarSource);
        foreach (["'date',", "'obfuscator',", "'image_modify',", "'image_draw',", "'soap',"] as $registration) {
            $this->assertStringContainsString($registration, $utilityRegistrarSource);
        }
        $this->assertStringNotContainsString("'database'", $source);
        $this->assertStringNotContainsString("'database_by_param'", $source);
        $this->assertStringContainsString("'session',", $sessionRegistrarSource);
        foreach (["'user',", "'current_user'", "'current_user_checked'", "'current_user_space'"] as $registration) {
            $this->assertStringContainsString($registration, $userRegistrarSource);
        }
        $this->assertStringNotContainsString('->factory(', $source);

        $this->assertStringContainsString('public application_service_graph_registrar $serviceGraphRegistrar,', $bundleSource);
        $this->assertStringContainsString('$serviceGraphRegistrar = $dependencyBundle->serviceGraphRegistrar;', $containerSource);
        $this->assertStringContainsString('application_service_graph_registration_context::fromBundles(', $containerSource);
        $this->assertStringContainsString('$serviceGraphRegistrar->register(', $containerSource);
        $this->assertStringNotContainsString("->factory('database'", $containerSource);
        $this->assertStringNotContainsString("->factory('current_user'", $containerSource);
        $this->assertStringNotContainsString('$userServiceCreator->createUserService(', $containerSource);
        $this->assertStringNotContainsString('callable $configServiceFactory,', $source);
    }

    public function testRegistrarClassIsInstantiable(): void
    {
        $this->assertInstanceOf(
            application_service_graph_registrar::class,
            new application_service_graph_registrar(
                new application_core_service_registrar(),
                new application_infrastructure_service_registrar(),
                new application_content_service_registrar(),
                new application_navigation_service_registrar(),
                new application_controller_service_registrar(),
                new application_client_service_registrar(),
                new application_pager_service_registrar(),
                new application_utility_service_registrar(),
                new application_session_service_registrar(),
                new application_user_service_registrar()
            )
        );
        $this->assertTrue(class_exists(application_service_graph_registration_context::class));
    }
}
