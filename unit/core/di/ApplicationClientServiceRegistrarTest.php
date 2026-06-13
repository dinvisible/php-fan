<?php

declare(strict_types=1);

use fan\core\di\application_client_service_registrar;
use fan\core\di\application_client_service_registrar_dependencies;
use PHPUnit\Framework\TestCase;

final class ApplicationClientServiceRegistrarTest extends TestCase
{
    public function testClientRegistrarOwnsClientServiceGraphRegistrations(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_client_service_registrar.php');
        $graphSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_service_graph_registrar.php');
        $dependencySource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_client_service_registrar_dependencies.php');
        $cookieDependencySource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_client_cookie_state_registrar_dependencies.php');
        $curlDependencySource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_client_curl_state_registrar_dependencies.php');
        $restDependencySource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_client_rest_state_registrar_dependencies.php');
        $dependencyProviderSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_container_dependency_provider.php');
        $registrarDefaultsProviderFactorySource = file_get_contents(dirname(__DIR__, 3) . '/core/factory/application_service_registrar_defaults_provider_factory.php');

        $this->assertIsString($source);
        $this->assertIsString($graphSource);
        $this->assertIsString($dependencySource);
        $this->assertIsString($cookieDependencySource);
        $this->assertIsString($curlDependencySource);
        $this->assertIsString($restDependencySource);
        $this->assertIsString($dependencyProviderSource);
        $this->assertIsString($registrarDefaultsProviderFactorySource);
        $this->assertStringContainsString('final class application_client_service_registrar', $source);
        $this->assertStringContainsString('final class application_client_service_registrar_dependencies', $dependencySource);
        $this->assertStringContainsString('final class application_client_cookie_state_registrar_dependencies', $cookieDependencySource);
        $this->assertStringContainsString('final class application_client_curl_state_registrar_dependencies', $curlDependencySource);
        $this->assertStringContainsString('final class application_client_rest_state_registrar_dependencies', $restDependencySource);
        foreach (['service_id::COOKIE', 'service_id::CURL', 'service_id::REST'] as $registration) {
            $this->assertStringContainsString($registration, $source);
        }
        foreach ([
            '$dependenciesFactory($container)->cookieState()',
            '$dependenciesFactory($container)->curlState()',
            '$dependenciesFactory($container)->restState()',
        ] as $dependencyCall) {
            $this->assertStringContainsString($dependencyCall, $source);
        }
        foreach ([
            'return $this->cookie->cookieState();',
            'return $this->curl->curlState();',
            'return $this->rest->restState();',
        ] as $dependencyCall) {
            $this->assertStringContainsString($dependencyCall, $dependencySource);
        }
        $this->assertStringContainsString('return $this->container->get(service_id::COOKIE_STATE);', $cookieDependencySource);
        $this->assertStringContainsString('return $this->container->get(service_id::CURL_STATE);', $curlDependencySource);
        $this->assertStringContainsString('return $this->container->get(service_id::REST_STATE);', $restDependencySource);
        foreach (['COOKIE_STATE', 'CURL_STATE', 'REST_STATE'] as $stateConstant) {
            $this->assertStringNotContainsString('->get(service_id::' . $stateConstant . ')', $source);
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
        $this->assertInstanceOf(
            application_client_service_registrar_dependencies::class,
            new application_client_service_registrar_dependencies($this->createStub(\fan\core\di\container_interface::class))
        );
    }
}
