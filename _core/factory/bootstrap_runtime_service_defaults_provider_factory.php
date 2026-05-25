<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\di\bootstrap_runtime_service_defaults_factory;
use fan\core\service\bootstrap_runtime;


final class bootstrap_runtime_service_defaults_provider_factory
{
    private \Closure $serviceExceptionFactory;
    private \Closure $runtimeServiceFactoryFactory;
    private \Closure $bootstrapRuntimeFactory;
    private \Closure $serviceExceptionFactoryProvider;
    private \Closure $runtimeServiceFactoryFactoryProvider;
    private \Closure $bootstrapRuntimeProvider;

    public function __construct(
        ?callable $serviceExceptionFactory = null,
        ?callable $runtimeServiceFactoryFactory = null,
        ?callable $bootstrapRuntimeFactory = null,
        ?callable $serviceExceptionFactoryProvider = null,
        ?callable $runtimeServiceFactoryFactoryProvider = null,
        ?callable $bootstrapRuntimeProvider = null
    ) {
        $this->serviceExceptionFactoryProvider = \Closure::fromCallable(
            $serviceExceptionFactoryProvider
                ?? static fn(): callable => new service_exception_factory()
        );
        $this->runtimeServiceFactoryFactoryProvider = \Closure::fromCallable(
            $runtimeServiceFactoryFactoryProvider
                ?? static fn(): callable => static fn(callable $runtimeFactory): callable => new bootstrap_runtime_service_factory($runtimeFactory)
        );
        $this->bootstrapRuntimeProvider = \Closure::fromCallable(
            $bootstrapRuntimeProvider
                ?? static fn(
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
                )
        );
        $this->serviceExceptionFactory = \Closure::fromCallable(
            $serviceExceptionFactory
                ?? fn(mixed ...$arguments): mixed => ($this->serviceExceptionFactoryProvider)()(...$arguments)
        );
        $this->runtimeServiceFactoryFactory = \Closure::fromCallable(
            $runtimeServiceFactoryFactory
                ?? fn(callable $runtimeFactory): callable => ($this->runtimeServiceFactoryFactoryProvider)()($runtimeFactory)
        );
        $this->bootstrapRuntimeFactory = \Closure::fromCallable(
            $bootstrapRuntimeFactory
                ?? fn(
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
                ): object => ($this->bootstrapRuntimeProvider)(
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
                )
        );
    }

    public function __invoke(): bootstrap_runtime_service_defaults_factory
    {
        $serviceExceptionFactory = $this->serviceExceptionFactory;
        $classNameResolver = static function (string|object $object): string {
            if (is_object($object)) {
                $object = get_class($object);
            }
            $parts = explode('\\', $object);

            return (string)end($parts);
        };
        $arrayValueReader = null;
        $arrayValueReader = static function (array|\ArrayAccess $array, mixed $key, mixed $default = null) use (&$arrayValueReader): mixed {
            if ($key === null) {
                return $default;
            }
            if (is_array($key)) {
                if ($key === []) {
                    return $default;
                }
                $firstKey = array_shift($key);
                if ($key !== []) {
                    return isset($array[$firstKey]) && (is_array($array[$firstKey]) || $array[$firstKey] instanceof \ArrayAccess)
                        ? $arrayValueReader($array[$firstKey], $key, $default)
                        : $default;
                }
                $key = $firstKey;
            }

            return $array[$key] ?? $default;
        };
        $runtimeServiceFactoryFactory = $this->runtimeServiceFactoryFactory;
        $bootstrapRuntimeFactory = $this->bootstrapRuntimeFactory;

        return new bootstrap_runtime_service_defaults_factory(
            static fn(): callable => $runtimeServiceFactoryFactory(
                static function (
                    ?object $serviceListenerState = null,
                    ?object $serviceSingleState = null,
                    ?object $viewLoaderState = null,
                    ?object $metaMakerState = null,
                    ?object $specFileImageRowState = null,
                    ?callable $serviceEngineFactory = null,
                    array $bootstrapOperations = [],
                    ?callable $serviceExceptionFactoryOverride = null,
                    ?callable $classNameResolverOverride = null,
                    ?callable $arrayValueReaderOverride = null
                ) use ($serviceExceptionFactory, $classNameResolver, $arrayValueReader, $bootstrapRuntimeFactory): object {
                    $serviceExceptionFactoryOverride ??= $serviceExceptionFactory;
                    $classNameResolverOverride ??= $classNameResolver;
                    $arrayValueReaderOverride ??= $arrayValueReader;

                    return $bootstrapRuntimeFactory(
                        $serviceListenerState,
                        $serviceSingleState,
                        $viewLoaderState,
                        $metaMakerState,
                        $specFileImageRowState,
                        $serviceEngineFactory,
                        $bootstrapOperations,
                        $serviceExceptionFactoryOverride,
                        $classNameResolverOverride,
                        $arrayValueReaderOverride
                    );
                }
            )
        );
    }
}
