<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_infrastructure_service_creator
{
    private \Closure $configRowFactoryFactory;

    public function __construct(?callable $configRowFactoryFactory = null)
    {
        $this->configRowFactoryFactory = \Closure::fromCallable(
            $configRowFactoryFactory
                ?? static function (
                    object $serializerOperations,
                    callable $serviceExceptionFactory,
                    callable $shortClassNameResolver
                ): callable {
                    throw new \RuntimeException('Config row factory factory is not configured for infrastructure service creator.');
                }
        );
    }

    public function createConfigService(
        container_interface $container,
        object $configState,
        callable $configServiceFactory,
        string $configType = 'service',
        string $sourceType = 'arr',
        ?object $serviceBootstrapRuntime = null,
        ?callable $serviceCacheFactory = null
    ): mixed {
        $config = $configState->getInstance($configType);
        if ($config === null) {
            $runtime = $serviceBootstrapRuntime ?? $container->get(service_id::BOOTSTRAP_RUNTIME);
            $className = self::getProjectServiceClassName('config');
            if (!class_exists($className)) {
                throw new \InvalidArgumentException('Service "config" does not expose a project class.');
            }

            $config = $configServiceFactory(
                $className,
                $configType,
                $sourceType,
                static fn(string $configType = 'service', string $sourceType = 'arr'): mixed => $container->get(service_id::CONFIG, $configType, $sourceType),
                static fn(): mixed => $container->get(service_id::CONFIG_CACHE),
                $configState,
                $runtime,
                $runtime,
                null,
                $serviceCacheFactory ?? static fn(string $type): mixed => $container->get(service_id::CACHE, $type),
                $container->get(service_id::PHP_ARRAY_FILE_LOADER),
                $this->configRowFactory($container->get(service_id::SERIALIZER_OPERATIONS), $runtime->serviceExceptionFactory(), $container->get(service_id::SHORT_CLASS_NAME_RESOLVER)),
                $container->get(service_id::CACHE_SOURCE_FILE_METADATA),
                $container->get(service_id::CONFIG_SOURCE_FILE_STORAGE),
                $container->get(service_id::SHORT_CLASS_NAME_RESOLVER)
            );
        }

        return $config;
    }

    public function createConfigCache(
        container_interface $container,
        object $cacheState,
        object $memcacheState,
        callable $cacheEngineFactory,
        callable $cacheServiceFactory
    ): mixed {
        try {
            $className = self::getProjectServiceClassName('cache');
            if ($cacheState->hasInstances()) {
                throw $this->createError500Exception(
                    $container,
                    'It\'s inpossible to get config-Instance after make another Instances.',
                    E_USER_ERROR
                );
            }
            if (!class_exists($className)) {
                throw new \InvalidArgumentException('Service "cache" does not expose a project class.');
            }

            $cache = $cacheServiceFactory(
                $className,
                $className::CONFIG_TYPE,
                $container->get(service_id::BOOTSTRAP_RUNTIME),
                static fn(): mixed => $container->get(service_id::ERROR),
                $cacheState,
                $memcacheState,
                $cacheEngineFactory,
                null,
                null,
                null,
                $container->get(service_id::CACHE_SOURCE_FILE_METADATA),
                $this->configCacheFatalExceptionFactory($container)
            );
            $cache->get('service');
        } catch (\Exception $exception) {
            $container->get(service_id::BOOTSTRAP_RUNTIME)->logError($exception->getMessage());
            return null;
        }

        return $cache;
    }

    public function createCacheService(
        container_interface $container,
        object $cacheState,
        object $memcacheState,
        callable $cacheEngineFactory,
        callable $cacheServiceFactory,
        mixed $type = null
    ): mixed {
        $className = self::getProjectServiceClassName('cache');
        if ($type === null) {
            $config = $container->get(service_id::CONFIG);
            $type = $config->get('cache')->get('DEFAULT_TYPE');
            if (empty($type)) {
                throw $this->createServiceFatalException($container, $config, 'Default CACHE-type doesn\'t set in config-file.');
            }
        }
        $type = (string)$type;
        if ($type === $className::CONFIG_TYPE) {
            throw $this->createError500Exception($container, 'It\'s inpossible to get config-Instance by usual way.', E_USER_ERROR);
        }

        $instance = $cacheState->getInstance($type);
        if ($instance === null) {
            if (!class_exists($className)) {
                throw new \InvalidArgumentException('Service "cache" does not expose a project class.');
            }

            $instance = $cacheServiceFactory(
                $className,
                $type,
                $container->get(service_id::BOOTSTRAP_RUNTIME),
                static fn(): mixed => $container->get(service_id::ERROR),
                $cacheState,
                $memcacheState,
                $cacheEngineFactory,
                $container->get(service_id::BOOTSTRAP_RUNTIME),
                $container->get(service_id::CONFIG),
                static fn(string $type): mixed => $container->get(service_id::CACHE, $type),
                $container->get(service_id::CACHE_SOURCE_FILE_METADATA),
                $this->configCacheFatalExceptionFactory($container)
            );
        }

        return $instance;
    }

    private function configCacheFatalExceptionFactory(container_interface $container): callable
    {
        return static function (string $message, int $code = E_USER_ERROR, ?\Throwable $previous = null) use ($container): \Throwable {
            $factory = $container->get(service_id::CORE_FATAL_EXCEPTION_FACTORY);
            if (!is_callable($factory)) {
                throw new \RuntimeException('Core fatal exception factory must be callable.');
            }

            return $factory(
                $message,
                code: $code,
                previous: $previous,
                requestInput: $container->get(service_id::REQUEST_INPUT),
                exceptionRuntimeLogger: $container->get(service_id::BOOTSTRAP_RUNTIME),
                exceptionHeaderWriter: $container->get(service_id::HEADER_WRITER)
            );
        };
    }

    private function createError500Exception(
        container_interface $container,
        string $message,
        int $code = E_USER_ERROR,
        ?\Throwable $previous = null
    ): \Throwable {
        $factory = $container->get(service_id::ERROR500_EXCEPTION_FACTORY);
        if (!is_callable($factory)) {
            throw new \RuntimeException('Error500 exception factory must be callable.');
        }

        $exception = $factory($message, $code, $previous);
        if (!$exception instanceof \Throwable) {
            throw new \UnexpectedValueException('Error500 exception factory must return a throwable.');
        }

        return $exception;
    }

    public function createJsonService(
        container_interface $container,
        object $state,
        callable $jsonServiceFactory,
        bool $useBase64 = false
    ): mixed {
        $useBase64 = !empty($useBase64);
        $instance = $state->getInstance($useBase64);
        if ($instance === null) {
            $className = self::getProjectServiceClassName('json');
            if (!class_exists($className)) {
                throw new \InvalidArgumentException('Service "json" does not expose a project class.');
            }

            $instance = $jsonServiceFactory(
                $className,
                $useBase64,
                static fn(): mixed => $container->get(service_id::ERROR),
                $container->get(service_id::BOOTSTRAP_RUNTIME),
                $container->get(service_id::CONFIG),
                static fn(string $type): mixed => $container->get(service_id::CACHE, $type)
            );
            $state->setInstance($useBase64, $instance);
        }

        return $instance;
    }

    public function createFileSystemService(
        container_interface $container,
        object $state,
        callable $fileSystemServiceFactory,
        ?string $srcPath = null,
        ?object $serviceBootstrapRuntime = null,
        ?object $serviceConfigurator = null,
        ?callable $serviceCacheFactory = null
    ): mixed {
        if (!$srcPath) {
            return null;
        }
        $serviceBootstrapRuntime ??= $container->get(service_id::BOOTSTRAP_RUNTIME);
        $serviceConfigurator ??= $container->get(service_id::CONFIG);
        $serviceCacheFactory ??= static fn(string $type): mixed => $container->get(service_id::CACHE, $type);
        $fullPath = $serviceBootstrapRuntime->parsePath($srcPath);
        $instance = $state->getInstance($fullPath);
        if ($instance === null) {
            $className = self::getProjectServiceClassName('file_system');
            if (!class_exists($className)) {
                throw new \InvalidArgumentException('Service "file_system" does not expose a project class.');
            }

            $instance = $fileSystemServiceFactory(
                $className,
                $fullPath,
                $container->get(service_id::FILE_SYSTEM_STORAGE),
                $serviceBootstrapRuntime,
                $serviceConfigurator,
                $serviceCacheFactory
            );
            $state->setInstance($fullPath, $instance);
        }

        return $instance;
    }

    private static function getProjectServiceClassName(string $serviceName): string
    {
        return '\fan\project\service\\' . trim($serviceName, " \t\n\r\0\x0B\\");
    }

    private function createServiceFatalException(
        container_interface $container,
        object $service,
        string $message,
        int $code = E_USER_ERROR,
        ?\Throwable $previous = null
    ): \Throwable
    {
        $runtime = $container->get(service_id::BOOTSTRAP_RUNTIME);
        if (!method_exists($runtime, 'serviceExceptionFactory')) {
            throw new \RuntimeException('Service exception factory is not configured for infrastructure service creator.');
        }

        $exception = ($runtime->serviceExceptionFactory())(
            '\fan\project\exception\service\fatal',
            $service,
            $message,
            $code,
            $previous
        );
        if (!$exception instanceof \Throwable) {
            throw new \UnexpectedValueException('Service exception factory must return a throwable object.');
        }

        return $exception;
    }

    private function configRowFactory(object $serializerOperations, callable $serviceExceptionFactory, callable $shortClassNameResolver): callable
    {
        $factory = ($this->configRowFactoryFactory)($serializerOperations, $serviceExceptionFactory, $shortClassNameResolver);
        if (!is_callable($factory)) {
            $actual = is_object($factory) ? get_class($factory) : gettype($factory);
            throw new \UnexpectedValueException('Config row factory factory returned "' . $actual . '".');
        }

        return $factory;
    }
}
