<?php

declare(strict_types=1);

use fan\core\di\application_container_creator_defaults_provider_factory;
use fan\core\di\application_container_factory_callable_factory;
use fan\core\di\application_container_factory_provider_defaults_provider_factory;
use fan\core\di\application_container_operations_defaults_provider_factory;
use fan\core\di\application_container_registrar_defaults_provider_factory;
use fan\core\di\application_container_registry_defaults_provider_factory;
use fan\core\bootstrap\context;
use PHPUnit\Framework\TestCase;
use fan\core\di\context_defaults_factory;
use fan\core\di\application_container_dependency_bundle;
use fan\core\di\application_container_factory;
use fan\core\di\application_service_factory_bundle;
use fan\core\di\container;


final class ApplicationContainerFactoryCallableFactoryTest extends TestCase
{
    public function testFactoryCreatesApplicationContainerFactoryCallable(): void
    {
        $containerFactory = (new application_container_factory_callable_factory())(
            (new application_container_operations_defaults_provider_factory())(),
            (new application_container_registry_defaults_provider_factory())(),
            (new application_container_factory_provider_defaults_provider_factory())(),
            (new application_container_registrar_defaults_provider_factory())(),
            (new application_container_creator_defaults_provider_factory())()
        );
        $context = new context(defaultFactoriesFactory: new context_defaults_factory());

        $this->assertIsCallable($containerFactory);
        $this->assertInstanceOf(container::class, $containerFactory($context));
    }

    public function testFactoryRejectsMissingBootstrapContext(): void
    {
        $containerFactory = (new application_container_factory_callable_factory())(
            (new application_container_operations_defaults_provider_factory())(),
            (new application_container_registry_defaults_provider_factory())(),
            (new application_container_factory_provider_defaults_provider_factory())(),
            (new application_container_registrar_defaults_provider_factory())(),
            (new application_container_creator_defaults_provider_factory())()
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Application container factory requires a bootstrap context.');

        $containerFactory();
    }

    public function testFactoryUsesInjectedContainerFactoryFactory(): void
    {
        $container = new container();
        $receivedFactoryBundle = null;
        $receivedDependencyBundle = null;
        $containerFactory = (new application_container_factory_callable_factory(
            static function (
                application_service_factory_bundle $factoryBundle,
                application_container_dependency_bundle $dependencyBundle
            ) use ($container, &$receivedFactoryBundle, &$receivedDependencyBundle): container {
                $receivedFactoryBundle = $factoryBundle;
                $receivedDependencyBundle = $dependencyBundle;

                return $container;
            }
        ))(
            (new application_container_operations_defaults_provider_factory())(),
            (new application_container_registry_defaults_provider_factory())(),
            (new application_container_factory_provider_defaults_provider_factory())(),
            (new application_container_registrar_defaults_provider_factory())(),
            (new application_container_creator_defaults_provider_factory())()
        );
        $context = new context(defaultFactoriesFactory: new context_defaults_factory());

        $this->assertSame($container, $containerFactory($context));
        $this->assertInstanceOf(application_service_factory_bundle::class, $receivedFactoryBundle);
        $this->assertInstanceOf(application_container_dependency_bundle::class, $receivedDependencyBundle);
    }

    public function testFactoryUsesInjectedApplicationContainerFactoryProvider(): void
    {
        $providerCalls = 0;
        $receivedFactoryBundle = null;
        $receivedDependencyBundle = null;
        $containerFactory = (new application_container_factory_callable_factory(
            null,
            static function (
                application_service_factory_bundle $factoryBundle,
                application_container_dependency_bundle $dependencyBundle
            ) use (&$providerCalls, &$receivedFactoryBundle, &$receivedDependencyBundle): application_container_factory {
                ++$providerCalls;
                $receivedFactoryBundle = $factoryBundle;
                $receivedDependencyBundle = $dependencyBundle;

                return new application_container_factory($factoryBundle, $dependencyBundle);
            }
        ))(
            (new application_container_operations_defaults_provider_factory())(),
            (new application_container_registry_defaults_provider_factory())(),
            (new application_container_factory_provider_defaults_provider_factory())(),
            (new application_container_registrar_defaults_provider_factory())(),
            (new application_container_creator_defaults_provider_factory())()
        );
        $context = new context(defaultFactoriesFactory: new context_defaults_factory());

        $this->assertInstanceOf(container::class, $containerFactory($context));
        $this->assertSame(1, $providerCalls);
        $this->assertInstanceOf(application_service_factory_bundle::class, $receivedFactoryBundle);
        $this->assertInstanceOf(application_container_dependency_bundle::class, $receivedDependencyBundle);
    }

    public function testSourceOwnsApplicationContainerFactoryCallableBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/application_container_factory_callable_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class application_container_factory_callable_factory', $source);
        $this->assertStringContainsString('private \Closure $containerFactoryFactory;', $source);
        $this->assertStringContainsString('private \Closure $applicationContainerFactoryProvider;', $source);
        $this->assertStringContainsString('?callable $containerFactoryFactory = null,', $source);
        $this->assertStringContainsString('?callable $applicationContainerFactoryProvider = null', $source);
        $this->assertStringContainsString('$this->applicationContainerFactoryProvider = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->containerFactoryFactory = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('public function __invoke(', $source);
        $this->assertStringContainsString('callable $bootstrapOperationsFactory,', $source);
        $this->assertStringContainsString('callable $registryDefaultsProviderFactory,', $source);
        $this->assertStringContainsString('callable $factoryProviderDefaultsProviderFactory,', $source);
        $this->assertStringContainsString('callable $registrarDefaultsProviderFactory,', $source);
        $this->assertStringContainsString('callable $creatorDefaultsProviderFactory', $source);
        $this->assertStringContainsString('$containerFactoryFactory = $this->containerFactoryFactory;', $source);
        $this->assertStringContainsString('return static function (?context $context = null) use (', $source);
        $this->assertStringContainsString('new application_service_factory_options(', $source);
        $this->assertStringContainsString('bootstrapOperationsFactory: \Closure::fromCallable($bootstrapOperationsFactory($context))', $source);
        $this->assertStringContainsString('new application_container_dependency_provider(', $source);
        $this->assertStringContainsString('application_container_dependency_bundle::fromProvider(', $source);
        $this->assertStringContainsString('application_service_factory_bundle::fromProviders(', $source);
        $this->assertStringContainsString('return $containerFactoryFactory($factoryBundle, $dependencyBundle);', $source);
        $this->assertStringContainsString('): application_container_factory => new application_container_factory($factoryBundle, $dependencyBundle)', $source);
        $this->assertStringContainsString('): container => ($this->applicationContainerFactoryProvider)($factoryBundle, $dependencyBundle)->create()', $source);
        $this->assertStringNotContainsString('private ?\Closure $containerFactoryFactory', $source);
        $this->assertStringNotContainsString('private ?\Closure $applicationContainerFactoryProvider', $source);
        $this->assertStringNotContainsString('private function containerFactoryFactory(): \Closure', $source);
        $this->assertStringNotContainsString('private function applicationContainerFactoryProvider(): \Closure', $source);
        $this->assertStringNotContainsString('return (new \fan\core\di\application_container_factory($factoryBundle, $dependencyBundle))->create();', $source);
        $this->assertStringNotContainsString('): \fan\core\di\container => (new \fan\core\di\application_container_factory($factoryBundle, $dependencyBundle))->create();', $source);
        $this->assertStringNotContainsString("application_container_factory::class => '../factory/application_container_factory.php'", $source);
        $this->assertStringNotContainsString("application_container_dependency_bundle::class => '../di/application_container_dependency_bundle.php'", $source);
        $this->assertStringNotContainsString("application_container_dependency_provider::class => '../di/application_container_dependency_provider.php'", $source);
        $this->assertStringNotContainsString("application_service_factory_bundle::class => '../factory/application_service_factory_bundle.php'", $source);
        $this->assertStringNotContainsString("application_service_graph_registration_context::class => '../di/application_service_graph_registration_context.php'", $source);
        $this->assertStringNotContainsString("application_service_factory_options::class => '../factory/application_service_factory_options.php'", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/' . \$fileName;", $source);
        $this->assertStringNotContainsString('(new \fan\core\di\application_registry_defaults_provider_factory())()', $source);
        $this->assertStringNotContainsString('(new \fan\core\di\application_factory_provider_defaults_provider_factory())(', $source);
        $this->assertStringNotContainsString('(new \fan\core\di\application_service_registrar_defaults_provider_factory())()', $source);
        $this->assertStringNotContainsString('(new \fan\core\di\application_service_creator_defaults_provider_factory())()', $source);
    }
}
