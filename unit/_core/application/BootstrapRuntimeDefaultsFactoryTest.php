<?php

declare(strict_types=1);

use fan\core\di\bootstrap_runtime_defaults_factory;
use fan\core\bootstrap\bootstrap_runtime_factory;
use fan\core\di\bootstrap_runtime_service_defaults_provider_factory;
use fan\core\di\bootstrap_runtime_state_defaults_provider_factory;
use fan\core\bootstrap\context;
use PHPUnit\Framework\TestCase;
use fan\core\di\bootstrap_operations_defaults_provider_factory;
use fan\core\di\context_defaults_factory;
use fan\core\service\bootstrap_runtime;


final class BootstrapRuntimeDefaultsFactoryTest extends TestCase
{
    public function testFactoryReturnsBootstrapRuntimeFactory(): void
    {
        $runtimeFactory = $this->defaultsFactory()();
        $context = new context(defaultFactoriesFactory: new context_defaults_factory());

        $this->assertIsCallable($runtimeFactory);
        $this->assertInstanceOf(bootstrap_runtime::class, $runtimeFactory($context));
    }

    public function testSourceOwnsBootstrapRuntimeDefaultAssemblyBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/bootstrap_runtime_defaults_factory.php');
        $contextDefaultsSource = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/context_defaults_factory.php');
        $contextCoreDefaultsSource = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/context_core_defaults_provider_factory.php');

        $this->assertIsString($source);
        $this->assertIsString($contextDefaultsSource);
        $this->assertIsString($contextCoreDefaultsSource);
        $this->assertStringContainsString('final class bootstrap_runtime_defaults_factory', $source);
        $this->assertStringContainsString('private \Closure $bootstrapRuntimeFactoryFactory;', $source);
        $this->assertStringContainsString('private \Closure $bootstrapOperationsFactoryFactory;', $source);
        $this->assertStringContainsString('private \Closure $bootstrapRuntimeServiceFactory;', $source);
        $this->assertStringContainsString('private \Closure $bootstrapRuntimeStateFactory;', $source);
        $this->assertStringContainsString('callable $bootstrapRuntimeFactoryFactory', $source);
        $this->assertStringContainsString('callable $bootstrapOperationsFactoryFactory', $source);
        $this->assertStringContainsString('$this->bootstrapRuntimeFactoryFactory = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->bootstrapOperationsFactoryFactory = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('?? static fn(', $source);
        $this->assertStringContainsString('): bootstrap_runtime_factory => new bootstrap_runtime_factory(', $source);
        $this->assertStringContainsString('($this->bootstrapRuntimeFactoryFactory)(', $source);
        $this->assertStringContainsString('($this->bootstrapOperationsFactoryFactory)($context)', $source);
        $this->assertStringContainsString('new bootstrap_runtime_factory(', $source);
        $this->assertStringNotContainsString('bootstrap_operations_defaults_factory::operationsFactory($context)', $source);
        $this->assertStringNotContainsString('new bootstrap_operations_factory(new context_bootstrap_operations($context))', $source);
        $this->assertStringNotContainsString('bootstrap_runtime_service_defaults_factory::runtimeServiceFactory()', $source);
        $this->assertStringNotContainsString('new \fan\core\di\bootstrap_runtime_service_factory($this->bootstrapRuntimeConstructor())', $source);
        $this->assertStringNotContainsString('private function bootstrapRuntimeConstructor(): callable', $source);
        $this->assertStringNotContainsString('bootstrap_runtime_state_defaults_factory::runtimeStateFactory()', $source);
        $this->assertStringNotContainsString('new \fan\core\service\bootstrap_runtime(', $source);
        $this->assertStringNotContainsString("'serviceListenerState' => new \\fan\\core\\service\\service_listener_state()", $source);
        $this->assertStringNotContainsString("'serviceSingleState' => new \\fan\\core\\service\\service_single_state()", $source);
        $this->assertStringNotContainsString("'viewLoaderState' => new \\fan\\core\\view\\router\\loader_state()", $source);
        $this->assertStringNotContainsString("'metaMakerState' => new \\fan\\core\\base\\meta\\maker_state()", $source);
        $this->assertStringNotContainsString("'specFileImageRowState' => new \\fan\\core\\base\\model\\spec_file\\image\\row_state()", $source);
        $this->assertStringNotContainsString('private function loadDefaultFactories(): void', $source);
        $this->assertStringNotContainsString('require_once', $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/bootstrap_operations_factory.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/context_bootstrap_operations.php';", $source);
        $this->assertStringNotContainsString("require_once dirname(__DIR__) . '/factory/bootstrap_runtime_service_factory.php';", $source);
        $this->assertStringNotContainsString("require_once dirname(__DIR__) . '/service/bootstrap_runtime.php';", $source);
        $this->assertStringNotContainsString("require_once dirname(__DIR__) . '/service/service_listener_state.php';", $source);
        $this->assertStringNotContainsString("require_once dirname(__DIR__) . '/service/service_single_state.php';", $source);
        $this->assertStringNotContainsString("require_once dirname(__DIR__) . '/view/router/loader_state.php';", $source);
        $this->assertStringNotContainsString("require_once dirname(__DIR__) . '/base/meta/maker_state.php';", $source);
        $this->assertStringNotContainsString("require_once dirname(__DIR__) . '/base/model/spec_file/image/row_state.php';", $source);
        $this->assertStringContainsString('$contextRuntimeDefaults = new bootstrap_runtime_defaults_factory();', $contextCoreDefaultsSource);
        $this->assertStringContainsString("'bootstrapRuntimeFactory' => \$contextRuntimeDefaults()", $contextCoreDefaultsSource);
        $this->assertStringNotContainsString("require_once __DIR__ . '/context_core_runtime_defaults_provider_factory.php';", $contextCoreDefaultsSource);
        $this->assertStringNotContainsString("require_once __DIR__ . '/context_runtime_defaults_provider_factory.php';", $contextCoreDefaultsSource);
        $this->assertStringNotContainsString("require_once __DIR__ . '/bootstrap_runtime_defaults_provider_factory.php';", $contextCoreDefaultsSource);
        $this->assertStringNotContainsString('$bootstrapRuntimeFactory = (new bootstrap_runtime_defaults_factory(', $contextCoreDefaultsSource);
        $this->assertStringNotContainsString('): bootstrap_runtime_factory => new bootstrap_runtime_factory(', $contextCoreDefaultsSource);
        $this->assertStringNotContainsString('$bootstrapOperationsDefaults = (new bootstrap_operations_defaults_provider_factory())();', $contextCoreDefaultsSource);
        $this->assertStringNotContainsString('static fn(context $context): callable => $bootstrapOperationsDefaults->operationsFactory($context)', $contextCoreDefaultsSource);
        $this->assertStringNotContainsString('(new bootstrap_runtime_service_defaults_provider_factory())()->runtimeServiceFactory()', $contextCoreDefaultsSource);
        $this->assertStringNotContainsString('(new bootstrap_runtime_state_defaults_provider_factory())()->runtimeStateFactory()', $contextCoreDefaultsSource);
        $this->assertStringNotContainsString("require_once __DIR__ . '/bootstrap_runtime_defaults_factory.php';", $contextCoreDefaultsSource);
        $this->assertStringNotContainsString("require_once __DIR__ . '/bootstrap_runtime_factory.php';", $contextCoreDefaultsSource);
        $this->assertStringNotContainsString("require_once __DIR__ . '/bootstrap_operations_defaults_provider_factory.php';", $contextCoreDefaultsSource);
        $this->assertStringNotContainsString("require_once __DIR__ . '/bootstrap_runtime_service_defaults_provider_factory.php';", $contextCoreDefaultsSource);
        $this->assertStringNotContainsString("require_once __DIR__ . '/bootstrap_runtime_state_defaults_provider_factory.php';", $contextCoreDefaultsSource);
        $this->assertStringNotContainsString('$bootstrapRuntimeDefaults = [', $contextDefaultsSource);
        $this->assertStringNotContainsString('private function bootstrapRuntimeConstructor(): callable', $contextDefaultsSource);
        $this->assertStringNotContainsString('new \fan\core\service\bootstrap_runtime(', $contextDefaultsSource);
    }

    private function defaultsFactory(): bootstrap_runtime_defaults_factory
    {
        return new bootstrap_runtime_defaults_factory(
            static fn(
                callable $bootstrapOperationsFactory,
                callable $bootstrapRuntimeServiceFactory,
                callable $bootstrapRuntimeStateFactory
            ): bootstrap_runtime_factory => new bootstrap_runtime_factory(
                $bootstrapOperationsFactory,
                $bootstrapRuntimeServiceFactory,
                $bootstrapRuntimeStateFactory
            ),
            static fn(context $context): callable => (new bootstrap_operations_defaults_provider_factory())()
                ->operationsFactory($context),
            (new bootstrap_runtime_service_defaults_provider_factory())()->runtimeServiceFactory(),
            (new bootstrap_runtime_state_defaults_provider_factory())()->runtimeStateFactory()
        );
    }
}
