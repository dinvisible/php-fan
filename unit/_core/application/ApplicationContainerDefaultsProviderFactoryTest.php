<?php

declare(strict_types=1);

use fan\core\di\application_container_defaults_factory;
use fan\core\di\application_container_defaults_provider_factory;
use fan\core\bootstrap\context;
use PHPUnit\Framework\TestCase;
use fan\core\di\context_defaults_factory;
use fan\core\di\container;


final class ApplicationContainerDefaultsProviderFactoryTest extends TestCase
{
    public function testFactoryCreatesApplicationContainerDefaultsProvider(): void
    {
        $provider = (new application_container_defaults_provider_factory())();
        $containerFactory = $provider->containerFactory();
        $context = new context(defaultFactoriesFactory: new context_defaults_factory());

        $this->assertInstanceOf(application_container_defaults_factory::class, $provider);
        $this->assertIsCallable($containerFactory);
        $this->assertInstanceOf(container::class, $containerFactory($context));
    }

    public function testFactoryRejectsMissingBootstrapContext(): void
    {
        $containerFactory = (new application_container_defaults_provider_factory())()->containerFactory();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Application container factory requires a bootstrap context.');

        $containerFactory();
    }

    public function testFactoryUsesInjectedCompositionFactories(): void
    {
        $container = new container();
        $received = [];
        $provider = (new application_container_defaults_provider_factory(
            bootstrapOperationsFactoryProvider: static fn(): callable => static fn(): array => ['operation' => 'bootstrap'],
            registryDefaultsProviderFactoryProvider: static fn(): callable => static fn(): object => (object)['name' => 'registry'],
            factoryProviderDefaultsProviderFactoryProvider: static fn(): callable => static fn(): object => (object)['name' => 'factory-provider'],
            registrarDefaultsProviderFactoryProvider: static fn(): callable => static fn(): object => (object)['name' => 'registrar'],
            creatorDefaultsProviderFactoryProvider: static fn(): callable => static fn(): object => (object)['name' => 'creator'],
            containerFactoryCallableFactory: static function (callable ...$factories) use (&$received, $container): callable {
                $received = $factories;

                return static fn(): container => $container;
            }
        ))();

        $containerFactory = $provider->containerFactory();

        $this->assertInstanceOf(application_container_defaults_factory::class, $provider);
        $this->assertCount(5, $received);
        $this->assertSame($container, $containerFactory());
        $this->assertSame(['operation' => 'bootstrap'], ($received[0])());
        $this->assertSame('registry', ($received[1])()->name);
        $this->assertSame('factory-provider', ($received[2])()->name);
        $this->assertSame('registrar', ($received[3])()->name);
        $this->assertSame('creator', ($received[4])()->name);
    }

    public function testSourceOwnsApplicationContainerDefaultAssemblyBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/application_container_defaults_provider_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class application_container_defaults_provider_factory', $source);
        $this->assertStringContainsString('public function __invoke(): application_container_defaults_factory', $source);
        $this->assertStringContainsString('public function __construct(', $source);
        $this->assertStringContainsString('private \Closure $bootstrapOperationsFactoryProvider;', $source);
        $this->assertStringContainsString('private \Closure $registryDefaultsProviderFactoryProvider;', $source);
        $this->assertStringContainsString('private \Closure $factoryProviderDefaultsProviderFactoryProvider;', $source);
        $this->assertStringContainsString('private \Closure $registrarDefaultsProviderFactoryProvider;', $source);
        $this->assertStringContainsString('private \Closure $creatorDefaultsProviderFactoryProvider;', $source);
        $this->assertStringContainsString('private \Closure $containerFactoryCallableFactory;', $source);
        $this->assertStringContainsString('$this->bootstrapOperationsFactoryProvider = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->containerFactoryCallableFactory = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('return new application_container_defaults_factory(', $source);
        $this->assertStringContainsString('$bootstrapOperationsFactory = ($this->bootstrapOperationsFactoryProvider)();', $source);
        $this->assertStringContainsString('$registryDefaultsProviderFactory = ($this->registryDefaultsProviderFactoryProvider)();', $source);
        $this->assertStringContainsString('$factoryProviderDefaultsProviderFactory = ($this->factoryProviderDefaultsProviderFactoryProvider)();', $source);
        $this->assertStringContainsString('$registrarDefaultsProviderFactory = ($this->registrarDefaultsProviderFactoryProvider)();', $source);
        $this->assertStringContainsString('$creatorDefaultsProviderFactory = ($this->creatorDefaultsProviderFactoryProvider)();', $source);
        $this->assertStringContainsString('$containerFactory = ($this->containerFactoryCallableFactory)(', $source);
        $this->assertStringContainsString('?? static fn(): callable => (new application_container_operations_defaults_provider_factory())()', $source);
        $this->assertStringContainsString('?? static fn(): callable => (new application_container_registry_defaults_provider_factory())()', $source);
        $this->assertStringContainsString('?? static fn(): callable => (new application_container_factory_provider_defaults_provider_factory())()', $source);
        $this->assertStringContainsString('?? static fn(): callable => (new application_container_registrar_defaults_provider_factory())()', $source);
        $this->assertStringContainsString('?? static fn(): callable => (new application_container_creator_defaults_provider_factory())()', $source);
        $this->assertStringNotContainsString('private ?\Closure $bootstrapOperationsFactoryProvider', $source);
        $this->assertStringNotContainsString('private function bootstrapOperationsFactoryProvider(): \Closure', $source);
        $this->assertStringNotContainsString('private function containerFactoryCallableFactory(): \Closure', $source);
        $this->assertStringNotContainsString('$bootstrapOperationsDefaults = (new bootstrap_operations_defaults_provider_factory())();', $source);
        $this->assertStringNotContainsString('$bootstrapOperationsDefaults = new bootstrap_operations_defaults_factory(', $source);
        $this->assertStringNotContainsString('static fn(object $operations): bootstrap_operations_factory => new bootstrap_operations_factory($operations)', $source);
        $this->assertStringNotContainsString('static fn(context $context): context_bootstrap_operations => new context_bootstrap_operations($context)', $source);
        $this->assertStringNotContainsString('new application_service_factory_options(', $source);
        $this->assertStringNotContainsString('bootstrapOperationsFactory: \Closure::fromCallable($bootstrapOperationsFactory($context))', $source);
        $this->assertStringNotContainsString('bootstrap_operations_defaults_factory::operationsFactory($context)', $source);
        $this->assertStringNotContainsString('new \fan\core\di\application_container_dependency_provider(', $source);
        $this->assertStringContainsString('$registryDefaultsProviderFactory,', $source);
        $this->assertStringNotContainsString('(new \fan\core\di\application_registry_defaults_provider_factory())()', $source);
        $this->assertStringContainsString('$factoryProviderDefaultsProviderFactory,', $source);
        $this->assertStringNotContainsString('(new \fan\core\di\application_factory_provider_defaults_provider_factory())($adapterRegistry)', $source);
        $this->assertStringContainsString('$registrarDefaultsProviderFactory,', $source);
        $this->assertStringNotContainsString('(new \fan\core\di\application_service_registrar_defaults_provider_factory())()', $source);
        $this->assertStringContainsString('$creatorDefaultsProviderFactory', $source);
        $this->assertStringNotContainsString('(new \fan\core\di\application_service_creator_defaults_provider_factory())()', $source);
        $this->assertStringNotContainsString('application_container_dependency_bundle::fromProvider(', $source);
        $this->assertStringNotContainsString('application_service_factory_bundle::fromProviders(', $source);
        $this->assertStringNotContainsString('new \fan\core\di\application_container_factory($factoryBundle, $dependencyBundle)', $source);
        $this->assertStringNotContainsString("application_container_defaults_factory::class => 'application_container_defaults_factory.php'", $source);
        $this->assertStringNotContainsString("application_container_factory_callable_factory::class => 'application_container_factory_callable_factory.php'", $source);
        $this->assertStringNotContainsString("\\fan\\core\\di\\application_container_factory::class => '../factory/application_container_factory.php'", $source);
        $this->assertStringNotContainsString("\\fan\\core\\di\\application_container_dependency_bundle::class => '../di/application_container_dependency_bundle.php'", $source);
        $this->assertStringNotContainsString("\\fan\\core\\di\\application_container_dependency_provider::class => '../di/application_container_dependency_provider.php'", $source);
        $this->assertStringNotContainsString("\\fan\\core\\di\\application_registry_defaults_provider_factory::class => '../factory/application_registry_defaults_provider_factory.php'", $source);
        $this->assertStringNotContainsString("\\fan\\core\\di\\application_factory_provider_defaults_provider_factory::class => '../factory/application_factory_provider_defaults_provider_factory.php'", $source);
        $this->assertStringNotContainsString("\\fan\\core\\di\\application_service_registrar_defaults_provider_factory::class => '../factory/application_service_registrar_defaults_provider_factory.php'", $source);
        $this->assertStringNotContainsString("\\fan\\core\\di\\application_service_creator_defaults_provider_factory::class => '../factory/application_service_creator_defaults_provider_factory.php'", $source);
        $this->assertStringNotContainsString("\\fan\\core\\di\\application_service_factory_bundle::class => '../factory/application_service_factory_bundle.php'", $source);
        $this->assertStringNotContainsString("\\fan\\core\\di\\application_service_graph_registration_context::class => '../di/application_service_graph_registration_context.php'", $source);
        $this->assertStringNotContainsString("\\fan\\core\\di\\application_service_factory_options::class => '../factory/application_service_factory_options.php'", $source);
        $this->assertStringNotContainsString("application_container_operations_defaults_provider_factory::class => 'application_container_operations_defaults_provider_factory.php'", $source);
        $this->assertStringNotContainsString("application_container_registry_defaults_provider_factory::class => 'application_container_registry_defaults_provider_factory.php'", $source);
        $this->assertStringNotContainsString("application_container_factory_provider_defaults_provider_factory::class => 'application_container_factory_provider_defaults_provider_factory.php'", $source);
        $this->assertStringNotContainsString("application_container_registrar_defaults_provider_factory::class => 'application_container_registrar_defaults_provider_factory.php'", $source);
        $this->assertStringNotContainsString("application_container_creator_defaults_provider_factory::class => 'application_container_creator_defaults_provider_factory.php'", $source);
        $this->assertStringNotContainsString("bootstrap_operations_defaults_provider_factory::class => 'bootstrap_operations_defaults_provider_factory.php'", $source);
        $this->assertStringNotContainsString("bootstrap_operations_defaults_factory::class => 'bootstrap_operations_defaults_factory.php'", $source);
        $this->assertStringNotContainsString("bootstrap_operations_factory::class => 'bootstrap_operations_factory.php'", $source);
        $this->assertStringNotContainsString("context_bootstrap_operations::class => 'context_bootstrap_operations.php'", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/' . \$fileName;", $source);
    }
}
