<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_infrastructure_service_creator
{
    private \Closure $configRowFactoryFactory;
    private \Closure $projectServiceClassExists;

    public function __construct(?callable $configRowFactoryFactory = null, ?callable $projectServiceClassExists = null)
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
        $this->projectServiceClassExists = \Closure::fromCallable(
            $projectServiceClassExists ?? static fn(string $className): bool => class_exists($className)
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
            $infrastructureDependencies = self::configCacheDependencies($container);
            $runtime = $serviceBootstrapRuntime ?? $infrastructureDependencies->bootstrapRuntime();
            $className = self::getProjectServiceClassName('config');
            if (!$this->projectServiceClassExists($className)) {
                throw new \InvalidArgumentException('Service "config" does not expose a project class.');
            }

            $config = $configServiceFactory(
                $className,
                $configType,
                $sourceType,
                $infrastructureDependencies->configFactory(),
                $infrastructureDependencies->configCacheFactory(),
                $configState,
                $runtime,
                $runtime,
                null,
                $serviceCacheFactory ?? $infrastructureDependencies->cacheFactory(),
                $infrastructureDependencies->phpArrayFileLoader(),
                $this->configRowFactory(
                    $infrastructureDependencies->serializerOperations(),
                    $runtime->serviceExceptionFactory(),
                    $infrastructureDependencies->shortClassNameResolver()
                ),
                $infrastructureDependencies->cacheSourceFileMetadata(),
                $infrastructureDependencies->configSourceFileStorage(),
                $infrastructureDependencies->shortClassNameResolver()
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
        $infrastructureDependencies = self::configCacheDependencies($container);
        try {
            $className = self::getProjectServiceClassName('cache');
            if ($cacheState->hasInstances()) {
                throw $this->createError500Exception(
                    $container,
                    'It\'s inpossible to get config-Instance after make another Instances.',
                    E_USER_ERROR
                );
            }
            if (!$this->projectServiceClassExists($className)) {
                throw new \InvalidArgumentException('Service "cache" does not expose a project class.');
            }

            $cache = $cacheServiceFactory(
                $className,
                $className::CONFIG_TYPE,
                $infrastructureDependencies->bootstrapRuntime(),
                $infrastructureDependencies->errorFactory(),
                $cacheState,
                $memcacheState,
                $cacheEngineFactory,
                null,
                null,
                null,
                $infrastructureDependencies->cacheSourceFileMetadata(),
                $this->configCacheFatalExceptionFactory($container, $infrastructureDependencies)
            );
            $cache->get('service');
        } catch (\Exception $exception) {
            $infrastructureDependencies->bootstrapRuntime()->logError($exception->getMessage());
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
        $infrastructureDependencies = self::configCacheDependencies($container);
        $className = self::getProjectServiceClassName('cache');
        if ($type === null) {
            $config = $infrastructureDependencies->config();
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
            if (!$this->projectServiceClassExists($className)) {
                throw new \InvalidArgumentException('Service "cache" does not expose a project class.');
            }

            $instance = $cacheServiceFactory(
                $className,
                $type,
                $infrastructureDependencies->bootstrapRuntime(),
                $infrastructureDependencies->errorFactory(),
                $cacheState,
                $memcacheState,
                $cacheEngineFactory,
                $infrastructureDependencies->bootstrapRuntime(),
                $infrastructureDependencies->config(),
                $infrastructureDependencies->cacheFactory(),
                $infrastructureDependencies->cacheSourceFileMetadata(),
                $this->configCacheFatalExceptionFactory($container, $infrastructureDependencies)
            );
        }

        return $instance;
    }

    private function configCacheFatalExceptionFactory(
        container_interface $container,
        ?application_infrastructure_config_cache_dependencies $infrastructureDependencies = null
    ): callable
    {
        $infrastructureDependencies ??= self::configCacheDependencies($container);
        return static function (string $message, int $code = E_USER_ERROR, ?\Throwable $previous = null) use ($infrastructureDependencies): \Throwable {
            $factory = $infrastructureDependencies->coreFatalExceptionFactory();
            if (!is_callable($factory)) {
                throw new \RuntimeException('Core fatal exception factory must be callable.');
            }

            return $factory(
                $message,
                code: $code,
                previous: $previous,
                requestInput: $infrastructureDependencies->requestInput(),
                exceptionRuntimeLogger: $infrastructureDependencies->bootstrapRuntime(),
                exceptionHeaderWriter: $infrastructureDependencies->headerWriter()
            );
        };
    }

    private function createError500Exception(
        container_interface $container,
        string $message,
        int $code = E_USER_ERROR,
        ?\Throwable $previous = null
    ): \Throwable {
        $factory = self::configCacheDependencies($container)->error500ExceptionFactory();
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
            $infrastructureDependencies = self::serviceDependencies($container);
            $className = self::getProjectServiceClassName('json');
            if (!$this->projectServiceClassExists($className)) {
                throw new \InvalidArgumentException('Service "json" does not expose a project class.');
            }

            $instance = $jsonServiceFactory(
                $className,
                $useBase64,
                $infrastructureDependencies->errorFactory(),
                $infrastructureDependencies->bootstrapRuntime(),
                $infrastructureDependencies->config(),
                $infrastructureDependencies->cacheFactory()
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
        $infrastructureDependencies = self::serviceDependencies($container);
        $serviceBootstrapRuntime ??= $infrastructureDependencies->bootstrapRuntime();
        $serviceConfigurator ??= $infrastructureDependencies->config();
        $serviceCacheFactory ??= $infrastructureDependencies->cacheFactory();
        $fullPath = $serviceBootstrapRuntime->parsePath($srcPath);
        $instance = $state->getInstance($fullPath);
        if ($instance === null) {
            $className = self::getProjectServiceClassName('file_system');
            if (!$this->projectServiceClassExists($className)) {
                throw new \InvalidArgumentException('Service "file_system" does not expose a project class.');
            }

            $instance = $fileSystemServiceFactory(
                $className,
                $fullPath,
                $infrastructureDependencies->fileSystemStorage(),
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

    private function projectServiceClassExists(string $className): bool
    {
        return (bool)($this->projectServiceClassExists)($className);
    }

    private function createServiceFatalException(
        container_interface $container,
        object $service,
        string $message,
        int $code = E_USER_ERROR,
        ?\Throwable $previous = null
    ): \Throwable
    {
        $runtime = self::configCacheDependencies($container)->bootstrapRuntime();
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

    private static function serviceDependencies(container_interface $container): application_infrastructure_service_dependencies
    {
        return new application_infrastructure_service_dependencies($container);
    }

    private static function configCacheDependencies(container_interface $container): application_infrastructure_config_cache_dependencies
    {
        return new application_infrastructure_config_cache_dependencies($container);
    }
}
