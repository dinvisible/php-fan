<?php

declare(strict_types=1);

use fan\core\di\bootstrap_runtime_service_factory;
use PHPUnit\Framework\TestCase;
use fan\core\service\bootstrap_runtime;
use fan\core\service\service_listener_state;

final class BootstrapRuntimeServiceFactoryTest extends TestCase
{
    public function testFactoryCreatesBootstrapRuntimeWithInjectedDependencies(): void
    {
        $listenerState = new service_listener_state();
        $engineFactory = static fn(string $class): object => (object)['class' => $class];
        $exceptionFactory = static fn(): \Throwable => new RuntimeException('service exception');
        $classNameResolver = static fn(object $object): string => get_class($object);
        $arrayValueReader = static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default;
        $operations = [
            'logError' => static fn(string $message): null => null,
        ];

        $runtime = (new bootstrap_runtime_service_factory(self::runtimeConstructor()))(
            $listenerState,
            null,
            null,
            null,
            null,
            $engineFactory,
            $operations,
            $exceptionFactory,
            $classNameResolver,
            $arrayValueReader
        );

        $this->assertInstanceOf(bootstrap_runtime::class, $runtime);
        $this->assertSame($listenerState, $runtime->serviceListenerState());
        $this->assertSame($engineFactory, $runtime->serviceEngineFactory());
        $this->assertSame($exceptionFactory, $runtime->serviceExceptionFactory());
        $this->assertSame($classNameResolver, $runtime->classNameResolver());
        $this->assertSame($arrayValueReader, $runtime->arrayValueReader());
    }
    private static function runtimeConstructor(): callable
    {
        return static fn(
            ?object $serviceListenerState = null,
            ?object $serviceSingleState = null,
            ?object $viewLoaderState = null,
            ?object $metaMakerState = null,
            ?object $specFileImageRowState = null,
            ?callable $serviceEngineFactory = null,
            array $bootstrapOperations = [],
            ?callable $serviceExceptionFactory = null,
            ?callable $classNameResolver = null,
            ?callable $arrayValueReader = null
        ): object => new bootstrap_runtime(
            $serviceListenerState,
            $serviceSingleState,
            $viewLoaderState,
            $metaMakerState,
            $specFileImageRowState,
            $serviceEngineFactory,
            $bootstrapOperations,
            $serviceExceptionFactory,
            $classNameResolver,
            $arrayValueReader
        );
    }
}
