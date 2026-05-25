<?php

declare(strict_types=1);

use fan\core\bootstrap\bootstrap_runtime_factory;
use PHPUnit\Framework\TestCase;
use fan\core\base\meta\maker_state;
use fan\core\base\model\spec_file\image\row_state;
use fan\core\di\bootstrap_runtime_service_factory;
use fan\core\service\bootstrap_runtime;
use fan\core\service\service_listener_state;
use fan\core\service\service_single_state;
use fan\core\view\router\loader_state;


final class BootstrapRuntimeFactoryTest extends TestCase
{
    public function testFactoryCreatesBootstrapRuntimeBoundaryObject(): void
    {
        $defaults = self::runtimeDefaults();
        $factory = new bootstrap_runtime_factory(
            static fn(): array => [],
            $defaults['bootstrapRuntimeServiceFactory'],
            $defaults['bootstrapRuntimeStateFactory']
        );

        $this->assertInstanceOf(bootstrap_runtime::class, $factory());
    }

    public function testFactoryAcceptsInjectedBootstrapOperationsFactory(): void
    {
        $calls = 0;
        $runtimeFactoryCalls = 0;
        $stateFactoryCalls = 0;
        $listenerState = new service_listener_state();
        $factory = new bootstrap_runtime_factory(
            static function () use (&$calls): array {
                ++$calls;

                return [
                    'logError' => static fn(string $message): null => null,
                ];
            },
            static function (
                ?object $serviceListenerState = null,
                ?object $serviceSingleState = null,
                ?object $viewLoaderState = null,
                ?object $metaMakerState = null,
                ?object $specFileImageRowState = null,
                ?callable $serviceEngineFactory = null,
                array $bootstrapOperations = []
            ) use (&$runtimeFactoryCalls, $listenerState): object {
                ++$runtimeFactoryCalls;

                return new bootstrap_runtime(
                    $serviceListenerState,
                    $serviceSingleState,
                    $viewLoaderState,
                    $metaMakerState,
                    $specFileImageRowState,
                    $serviceEngineFactory,
                    $bootstrapOperations
                );
            },
            static function () use (&$stateFactoryCalls, $listenerState): array {
                ++$stateFactoryCalls;

                return ['serviceListenerState' => $listenerState];
            }
        );

        $runtime = $factory();

        $this->assertInstanceOf(bootstrap_runtime::class, $runtime);
        $this->assertSame($listenerState, $runtime->serviceListenerState());
        $this->assertSame(1, $calls);
        $this->assertSame(1, $runtimeFactoryCalls);
        $this->assertSame(1, $stateFactoryCalls);
    }

    public function testFactoryRejectsInvalidBootstrapOperationsFactory(): void
    {
        $factory = new bootstrap_runtime_factory(
            static fn(): string => 'not-array',
            static fn(array $bootstrapOperations = []): object => new stdClass(),
            static fn(): array => []
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Bootstrap operations factory must return an array.');

        $factory();
    }

    public function testFactoryRejectsInvalidBootstrapRuntimeStateFactory(): void
    {
        $factory = new bootstrap_runtime_factory(
            static fn(): array => [],
            static fn(array $bootstrapOperations = []): object => new stdClass(),
            static fn(): string => 'not-array'
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Bootstrap runtime state factory must return an array.');

        $factory();
    }

    public function testSourceOwnsBootstrapRuntimeBoundaryConstruction(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/bootstrap_runtime_factory.php');
        $contextDefaultsSource = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/context_defaults_factory.php');
        $contextCoreDefaultsSource = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/context_core_defaults_provider_factory.php');

        $this->assertIsString($source);
        $this->assertIsString($contextDefaultsSource);
        $this->assertIsString($contextCoreDefaultsSource);
        $this->assertStringContainsString('final class bootstrap_runtime_factory', $source);
        $this->assertStringContainsString('private \Closure $bootstrapOperationsFactory;', $source);
        $this->assertStringContainsString('private \Closure $bootstrapRuntimeServiceFactory;', $source);
        $this->assertStringContainsString('private \Closure $bootstrapRuntimeStateFactory;', $source);
        $this->assertStringContainsString('callable $bootstrapOperationsFactory', $source);
        $this->assertStringContainsString('callable $bootstrapRuntimeServiceFactory', $source);
        $this->assertStringContainsString('callable $bootstrapRuntimeStateFactory', $source);
        $this->assertStringContainsString('\Closure::fromCallable($bootstrapOperationsFactory)', $source);
        $this->assertStringContainsString('\Closure::fromCallable($bootstrapRuntimeStateFactory)', $source);
        $this->assertStringNotContainsString('defaultFactoriesFactory', $source);
        $this->assertStringNotContainsString('new bootstrap_runtime_defaults_factory()', $source);
        $this->assertStringContainsString('$contextRuntimeDefaults = new bootstrap_runtime_defaults_factory();', $contextCoreDefaultsSource);
        $this->assertStringContainsString("'bootstrapRuntimeFactory' => \$contextRuntimeDefaults()", $contextCoreDefaultsSource);
        $this->assertStringNotContainsString('$bootstrapRuntimeFactory = (new bootstrap_runtime_defaults_factory(', $contextCoreDefaultsSource);
        $this->assertStringNotContainsString('): bootstrap_runtime_factory => new bootstrap_runtime_factory(', $contextCoreDefaultsSource);
        $this->assertStringNotContainsString('$bootstrapOperationsDefaults = (new bootstrap_operations_defaults_provider_factory())();', $contextCoreDefaultsSource);
        $this->assertStringNotContainsString('static fn(context $context): callable => $bootstrapOperationsDefaults->operationsFactory($context)', $contextCoreDefaultsSource);
        $this->assertStringNotContainsString('static fn(context $context): callable => (new bootstrap_operations_defaults_factory(', $contextCoreDefaultsSource);
        $this->assertStringNotContainsString('static fn(object $operations): bootstrap_operations_factory => new bootstrap_operations_factory($operations)', $contextCoreDefaultsSource);
        $this->assertStringNotContainsString('static fn(context $context): context_bootstrap_operations => new context_bootstrap_operations($context)', $contextCoreDefaultsSource);
        $this->assertStringNotContainsString('new bootstrap_operations_factory()', $source);
        $this->assertStringNotContainsString('new \fan\core\di\bootstrap_runtime_service_factory()', $source);
        $this->assertStringNotContainsString('new bootstrap_operations_factory(new bootstrap_static_operations())', $contextDefaultsSource);
        $this->assertStringNotContainsString("'bootstrapOperationsFactory' =>", $contextDefaultsSource);
        $this->assertStringNotContainsString("'bootstrapRuntimeServiceFactory' => \$this->bootstrapRuntimeServiceFactory()", $contextDefaultsSource);
        $this->assertStringNotContainsString('new \fan\core\di\bootstrap_runtime_service_factory($this->bootstrapRuntimeConstructor())', $contextDefaultsSource);
        $this->assertStringNotContainsString("'bootstrapRuntimeStateFactory' => static fn(): array => [", $contextDefaultsSource);
        $this->assertStringNotContainsString('new \fan\core\service\bootstrap_runtime(', $source);
        $this->assertStringContainsString('Bootstrap operations factory must return an array.', $source);
        $this->assertStringContainsString('Bootstrap runtime state factory must return an array.', $source);
        $this->assertStringNotContainsString('\bootstrap::', $source);
    }

    private static function runtimeDefaults(): array
    {
        return [
            'bootstrapRuntimeServiceFactory' => new bootstrap_runtime_service_factory(
                static fn(
                    ?object $serviceListenerState = null,
                    ?object $serviceSingleState = null,
                    ?object $viewLoaderState = null,
                    ?object $metaMakerState = null,
                    ?object $specFileImageRowState = null,
                    ?callable $serviceEngineFactory = null,
                    array $bootstrapOperations = []
                ): object => new bootstrap_runtime(
                    $serviceListenerState,
                    $serviceSingleState,
                    $viewLoaderState,
                    $metaMakerState,
                    $specFileImageRowState,
                    $serviceEngineFactory,
                    $bootstrapOperations
                )
            ),
            'bootstrapRuntimeStateFactory' => static fn(): array => [
                'serviceListenerState' => new service_listener_state(),
                'serviceSingleState' => new service_single_state(),
                'viewLoaderState' => new loader_state(),
                'metaMakerState' => new maker_state(),
                'specFileImageRowState' => new row_state(),
            ],
        ];
    }
}
