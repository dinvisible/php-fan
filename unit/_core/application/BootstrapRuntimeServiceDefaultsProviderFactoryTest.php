<?php

declare(strict_types=1);

use fan\core\di\bootstrap_runtime_service_defaults_factory;
use fan\core\di\bootstrap_runtime_service_defaults_provider_factory;
use PHPUnit\Framework\TestCase;
use fan\core\service\bootstrap_runtime;


final class BootstrapRuntimeServiceDefaultsProviderFactoryTest extends TestCase
{
    public function testFactoryCreatesBootstrapRuntimeServiceDefaultsProvider(): void
    {
        $provider = (new bootstrap_runtime_service_defaults_provider_factory())();
        $runtimeServiceFactory = $provider->runtimeServiceFactory();

        $this->assertInstanceOf(bootstrap_runtime_service_defaults_factory::class, $provider);
        $this->assertIsCallable($runtimeServiceFactory);
        $runtime = $runtimeServiceFactory();
        $this->assertInstanceOf(bootstrap_runtime::class, $runtime);
        $this->assertIsCallable($runtime->serviceExceptionFactory());
        $this->assertIsCallable($runtime->classNameResolver());
        $this->assertIsCallable($runtime->arrayValueReader());
    }

    public function testFactoryUsesInjectedRuntimeServiceDependencies(): void
    {
        $serviceExceptionFactory = static fn(): \Throwable => new RuntimeException('service fatal');
        $runtimeFactoryCalls = [];
        $expectedRuntime = (object)['name' => 'runtime'];
        $provider = (new bootstrap_runtime_service_defaults_provider_factory(
            serviceExceptionFactory: $serviceExceptionFactory,
            runtimeServiceFactoryFactory: static function (callable $runtimeFactory) use (&$runtimeFactoryCalls, $expectedRuntime): callable {
                $runtimeFactoryCalls[] = $runtimeFactory;

                return static fn(mixed ...$arguments): object => $expectedRuntime;
            }
        ))();

        $runtimeServiceFactory = $provider->runtimeServiceFactory();

        $this->assertInstanceOf(bootstrap_runtime_service_defaults_factory::class, $provider);
        $this->assertIsCallable($runtimeServiceFactory);
        $this->assertSame($expectedRuntime, $runtimeServiceFactory());
        $this->assertCount(1, $runtimeFactoryCalls);
        $this->assertIsCallable($runtimeFactoryCalls[0]);
        $this->assertInstanceOf(
            bootstrap_runtime::class,
            ($runtimeFactoryCalls[0])(serviceExceptionFactoryOverride: $serviceExceptionFactory)
        );
    }

    public function testFactoryUsesInjectedBootstrapRuntimeFactory(): void
    {
        $serviceExceptionFactory = static fn(): \Throwable => new RuntimeException('service fatal');
        $expectedRuntime = (object)['name' => 'runtime'];
        $received = [];
        $provider = (new bootstrap_runtime_service_defaults_provider_factory(
            serviceExceptionFactory: $serviceExceptionFactory,
            bootstrapRuntimeFactory: static function (
                ?object $serviceListenerState = null,
                ?object $serviceSingleState = null,
                ?object $viewLoaderState = null,
                ?object $metaMakerState = null,
                ?object $specFileImageRowState = null,
                ?callable $serviceEngineFactory = null,
                array $bootstrapOperations = [],
                ?callable $serviceExceptionFactoryOverride = null,
                ?callable $classNameResolver = null,
                ?callable $arrayValueReader = null
            ) use ($expectedRuntime, &$received): object {
                $received = [
                    $serviceListenerState,
                    $serviceSingleState,
                    $viewLoaderState,
                    $metaMakerState,
                    $specFileImageRowState,
                    $serviceEngineFactory,
                    $bootstrapOperations,
                    $serviceExceptionFactoryOverride,
                    $classNameResolver,
                    $arrayValueReader,
                ];

                return $expectedRuntime;
            }
        ))();
        $runtimeServiceFactory = $provider->runtimeServiceFactory();
        $states = [
            (object)['name' => 'listener'],
            (object)['name' => 'single'],
            (object)['name' => 'view'],
            (object)['name' => 'meta'],
            (object)['name' => 'spec'],
        ];
        $serviceEngineFactory = static fn(): object => (object)['name' => 'engine'];
        $operations = ['operation' => 'bootstrap'];

        $runtime = $runtimeServiceFactory(
            $states[0],
            $states[1],
            $states[2],
            $states[3],
            $states[4],
            $serviceEngineFactory,
            $operations
        );

        $this->assertSame($expectedRuntime, $runtime);
        $this->assertSame($states, array_slice($received, 0, 5));
        $this->assertSame($serviceEngineFactory, $received[5]);
        $this->assertSame($operations, $received[6]);
        $this->assertSame($serviceExceptionFactory, $received[7]);
        $this->assertIsCallable($received[8]);
        $this->assertIsCallable($received[9]);
    }

    public function testFactoryUsesInjectedDefaultBootstrapRuntimeProvider(): void
    {
        $serviceExceptionFactory = static fn(): \Throwable => new RuntimeException('service fatal');
        $expectedRuntime = (object)['name' => 'runtime-provider'];
        $providerCalls = 0;
        $received = [];
        $provider = (new bootstrap_runtime_service_defaults_provider_factory(
            serviceExceptionFactory: $serviceExceptionFactory,
            runtimeServiceFactoryFactory: static fn(callable $runtimeFactory): callable => static fn(mixed ...$arguments): object => $runtimeFactory(...$arguments),
            bootstrapRuntimeProvider: static function (
                ?object $serviceListenerState = null,
                ?object $serviceSingleState = null,
                ?object $viewLoaderState = null,
                ?object $metaMakerState = null,
                ?object $specFileImageRowState = null,
                ?callable $serviceEngineFactory = null,
                array $bootstrapOperations = [],
                ?callable $serviceExceptionFactoryOverride = null,
                ?callable $classNameResolver = null,
                ?callable $arrayValueReader = null
            ) use (&$providerCalls, &$received, $expectedRuntime): object {
                ++$providerCalls;
                $received = [
                    $serviceListenerState,
                    $serviceSingleState,
                    $viewLoaderState,
                    $metaMakerState,
                    $specFileImageRowState,
                    $serviceEngineFactory,
                    $bootstrapOperations,
                    $serviceExceptionFactoryOverride,
                    $classNameResolver,
                    $arrayValueReader,
                ];

                return $expectedRuntime;
            }
        ))();
        $runtimeServiceFactory = $provider->runtimeServiceFactory();
        $states = [
            (object)['name' => 'listener'],
            (object)['name' => 'single'],
            (object)['name' => 'view'],
            (object)['name' => 'meta'],
            (object)['name' => 'spec'],
        ];
        $serviceEngineFactory = static fn(): object => (object)['name' => 'engine'];
        $operations = ['operation' => 'bootstrap'];

        $runtime = $runtimeServiceFactory(
            $states[0],
            $states[1],
            $states[2],
            $states[3],
            $states[4],
            $serviceEngineFactory,
            $operations
        );

        $this->assertSame($expectedRuntime, $runtime);
        $this->assertSame(1, $providerCalls);
        $this->assertSame($states, array_slice($received, 0, 5));
        $this->assertSame($serviceEngineFactory, $received[5]);
        $this->assertSame($operations, $received[6]);
        $this->assertSame($serviceExceptionFactory, $received[7]);
        $this->assertIsCallable($received[8]);
        $this->assertIsCallable($received[9]);
    }

    public function testFactoryUsesInjectedDefaultFactoryProviders(): void
    {
        $serviceExceptionFactory = static fn(): \Throwable => new RuntimeException('provider fatal');
        $expectedRuntime = (object)['name' => 'runtime'];
        $runtimeFactoryCalls = 0;
        $provider = (new bootstrap_runtime_service_defaults_provider_factory(
            serviceExceptionFactoryProvider: static fn(): callable => $serviceExceptionFactory,
            runtimeServiceFactoryFactoryProvider: static function () use (&$runtimeFactoryCalls, $expectedRuntime): callable {
                return static function (callable $runtimeFactory) use (&$runtimeFactoryCalls, $expectedRuntime): callable {
                    $runtimeFactoryCalls++;
                    $runtime = $runtimeFactory(serviceExceptionFactoryOverride: null);

                    return static fn(): object => is_object($runtime) ? $expectedRuntime : throw new RuntimeException('Runtime factory failed.');
                };
            }
        ))();

        $runtimeServiceFactory = $provider->runtimeServiceFactory();

        $this->assertSame($expectedRuntime, $runtimeServiceFactory());
        $this->assertSame(1, $runtimeFactoryCalls);
    }

    public function testSourceOwnsBootstrapRuntimeServiceDefaultAssemblyBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/bootstrap_runtime_service_defaults_provider_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class bootstrap_runtime_service_defaults_provider_factory', $source);
        $this->assertStringContainsString('public function __invoke(): bootstrap_runtime_service_defaults_factory', $source);
        $this->assertStringContainsString('private \Closure $serviceExceptionFactory;', $source);
        $this->assertStringContainsString('private \Closure $runtimeServiceFactoryFactory;', $source);
        $this->assertStringContainsString('private \Closure $bootstrapRuntimeFactory;', $source);
        $this->assertStringContainsString('private \Closure $serviceExceptionFactoryProvider;', $source);
        $this->assertStringContainsString('private \Closure $runtimeServiceFactoryFactoryProvider;', $source);
        $this->assertStringContainsString('private \Closure $bootstrapRuntimeProvider;', $source);
        $this->assertStringContainsString('public function __construct(', $source);
        $this->assertStringContainsString('?callable $bootstrapRuntimeProvider = null', $source);
        $this->assertStringContainsString('$this->serviceExceptionFactoryProvider = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->runtimeServiceFactoryFactoryProvider = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->bootstrapRuntimeProvider = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->serviceExceptionFactory = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->runtimeServiceFactoryFactory = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->bootstrapRuntimeFactory = \Closure::fromCallable(', $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/bootstrap_runtime_service_defaults_factory.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/../factory/bootstrap_runtime_service_factory.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/../factory/service_exception_factory.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/../service/bootstrap_runtime.php';", $source);
        $this->assertStringContainsString('return new bootstrap_runtime_service_defaults_factory(', $source);
        $this->assertStringContainsString('$serviceExceptionFactory = $this->serviceExceptionFactory;', $source);
        $this->assertStringContainsString('$classNameResolver = static function (string|object $object): string', $source);
        $this->assertStringContainsString('$arrayValueReader = static function (array|\ArrayAccess $array, mixed $key, mixed $default = null) use (&$arrayValueReader): mixed', $source);
        $this->assertStringNotContainsString('\array_val($array, $key, $default)', $source);
        $this->assertStringContainsString('$runtimeServiceFactoryFactory = $this->runtimeServiceFactoryFactory;', $source);
        $this->assertStringContainsString('$bootstrapRuntimeFactory = $this->bootstrapRuntimeFactory;', $source);
        $this->assertStringContainsString('static fn(): callable => $runtimeServiceFactoryFactory(', $source);
        $this->assertStringContainsString('?? fn(mixed ...$arguments): mixed => ($this->serviceExceptionFactoryProvider)()(...$arguments)', $source);
        $this->assertStringContainsString('?? fn(callable $runtimeFactory): callable => ($this->runtimeServiceFactoryFactoryProvider)()($runtimeFactory)', $source);
        $this->assertStringContainsString('?? static fn(): callable => new service_exception_factory()', $source);
        $this->assertStringContainsString('?? static fn(): callable => static fn(callable $runtimeFactory): callable => new bootstrap_runtime_service_factory($runtimeFactory)', $source);
        $this->assertStringNotContainsString('?? new \fan\core\di\service_exception_factory()', $source);
        $this->assertStringNotContainsString('?? static fn(callable $runtimeFactory): callable => new bootstrap_runtime_service_factory($runtimeFactory)', $source);
        $this->assertStringContainsString('): object {', $source);
        $this->assertStringContainsString('$serviceExceptionFactoryOverride ??= $serviceExceptionFactory;', $source);
        $this->assertStringContainsString('$classNameResolverOverride ??= $classNameResolver;', $source);
        $this->assertStringContainsString('$arrayValueReaderOverride ??= $arrayValueReader;', $source);
        $this->assertStringContainsString('return $bootstrapRuntimeFactory(', $source);
        $this->assertStringContainsString('): object => ($this->bootstrapRuntimeProvider)(', $source);
        $this->assertStringContainsString('new bootstrap_runtime(', $source);
        $this->assertStringNotContainsString('private ?\Closure $serviceExceptionFactory', $source);
        $this->assertStringNotContainsString('private ?\Closure $runtimeServiceFactoryFactory', $source);
        $this->assertStringNotContainsString('private ?\Closure $bootstrapRuntimeFactory', $source);
        $this->assertStringNotContainsString('private ?\Closure $serviceExceptionFactoryProvider', $source);
        $this->assertStringNotContainsString('private ?\Closure $runtimeServiceFactoryFactoryProvider', $source);
        $this->assertStringNotContainsString('private ?\Closure $bootstrapRuntimeProvider', $source);
        $this->assertStringNotContainsString('$serviceExceptionFactory === null ? null : \Closure::fromCallable($serviceExceptionFactory)', $source);
        $this->assertStringNotContainsString('$runtimeServiceFactoryFactory === null ? null : \Closure::fromCallable($runtimeServiceFactoryFactory)', $source);
        $this->assertStringNotContainsString('$bootstrapRuntimeFactory === null ? null : \Closure::fromCallable($bootstrapRuntimeFactory)', $source);
        $this->assertStringNotContainsString('$serviceExceptionFactoryProvider === null ? null : \Closure::fromCallable($serviceExceptionFactoryProvider)', $source);
        $this->assertStringNotContainsString('$runtimeServiceFactoryFactoryProvider === null ? null : \Closure::fromCallable($runtimeServiceFactoryFactoryProvider)', $source);
        $this->assertStringNotContainsString('$bootstrapRuntimeProvider === null ? null : \Closure::fromCallable($bootstrapRuntimeProvider)', $source);
        $this->assertStringNotContainsString('private function serviceExceptionFactory(): callable', $source);
        $this->assertStringNotContainsString('private function runtimeServiceFactoryFactory(): callable', $source);
        $this->assertStringNotContainsString('private function serviceExceptionFactoryProvider(): callable', $source);
        $this->assertStringNotContainsString('private function runtimeServiceFactoryFactoryProvider(): callable', $source);
        $this->assertStringNotContainsString('private function bootstrapRuntimeFactory(): callable', $source);
        $this->assertStringNotContainsString('private function bootstrapRuntimeProvider(): callable', $source);
    }
}
