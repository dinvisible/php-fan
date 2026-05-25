<?php

declare(strict_types=1);

namespace fan\core\bootstrap;

use fan\core\di\container_interface;

class context
{
    private state $state;

    private \Closure $containerFactory;

    private \Closure $requestInputFactory;

    private \Closure $bootstrapRuntimeFactory;

    private \Closure $zendAutoloaderLoaderFactory;

    private \Closure $bootstrapLoaderFileStorageFactory;

    private \Closure $bootstrapObjectFactory;

    private \Closure $phpArrayFileLoader;

    private \Closure $bootstrapConfigLoader;

    private \Closure $bootstrapErrorHandlerSetup;

    private \Closure $phpRuntimeSettingsFactory;

    private \Closure $errorHandlerRegistrar;

    private \Closure $errorHandlerSetup;

    private \Closure $errorLogger;

    public function __construct(
        ?state $state = null,
        ?callable $containerFactory = null,
        ?callable $requestInputFactory = null,
        ?callable $bootstrapRuntimeFactory = null,
        ?callable $zendAutoloaderLoaderFactory = null,
        ?callable $bootstrapLoaderFileStorageFactory = null,
        ?callable $bootstrapObjectFactory = null,
        ?callable $phpArrayFileLoader = null,
        ?callable $bootstrapConfigLoader = null,
        ?callable $bootstrapErrorHandlerSetup = null,
        ?callable $phpRuntimeSettingsFactory = null,
        ?callable $errorHandlerRegistrar = null,
        ?callable $errorHandlerSetup = null,
        ?callable $errorLogger = null,
        ?callable $defaultFactoriesFactory = null
    ) {
        $defaultFactories = self::injectedDefaultFactories($defaultFactoriesFactory);
        $this->state = $state ?? self::createState($defaultFactories['stateFactory']);
        $this->containerFactory = \Closure::fromCallable($containerFactory ?? $defaultFactories['containerFactory']);
        $this->requestInputFactory = \Closure::fromCallable($requestInputFactory ?? $defaultFactories['requestInputFactory']);
        $this->bootstrapRuntimeFactory = \Closure::fromCallable($bootstrapRuntimeFactory ?? $defaultFactories['bootstrapRuntimeFactory']);
        $this->zendAutoloaderLoaderFactory = \Closure::fromCallable(
            $zendAutoloaderLoaderFactory ?? $defaultFactories['zendAutoloaderLoaderFactory']
        );
        $this->bootstrapLoaderFileStorageFactory = \Closure::fromCallable(
            $bootstrapLoaderFileStorageFactory ?? $defaultFactories['bootstrapLoaderFileStorageFactory']
        );
        $this->bootstrapObjectFactory = \Closure::fromCallable($bootstrapObjectFactory ?? $defaultFactories['bootstrapObjectFactory']);
        $this->phpArrayFileLoader = \Closure::fromCallable(
            $phpArrayFileLoader ?? fn(string $path, mixed $default = null): mixed => ($this->container()->get('php_array_file_loader'))($path, $default)
        );
        $this->bootstrapConfigLoader = \Closure::fromCallable(
            $bootstrapConfigLoader ?? $defaultFactories['bootstrapConfigLoader']
        );
        $this->bootstrapErrorHandlerSetup = \Closure::fromCallable(
            $bootstrapErrorHandlerSetup ?? $defaultFactories['bootstrapErrorHandlerSetup']
        );
        $this->phpRuntimeSettingsFactory = \Closure::fromCallable(
            $phpRuntimeSettingsFactory ?? $defaultFactories['phpRuntimeSettingsFactory']
        );
        $this->errorHandlerRegistrar = \Closure::fromCallable(
            $errorHandlerRegistrar ?? $defaultFactories['errorHandlerRegistrar']
        );
        $this->errorHandlerSetup = \Closure::fromCallable($errorHandlerSetup ?? $defaultFactories['errorHandlerSetup']);
        $this->errorLogger = \Closure::fromCallable($errorLogger ?? $defaultFactories['errorLogger']);
    }

    public function state(): state
    {
        return $this->state;
    }

    public function container(): container_interface
    {
        $container = $this->state->container();
        if ($container === null) {
            $container = ($this->containerFactory)($this);
            if (!$container instanceof container_interface) {
                throw new \RuntimeException('Bootstrap container factory must return a container.');
            }
            $this->state->setContainer($container);
        }

        return $container;
    }

    public function requestInput(): object
    {
        $input = ($this->requestInputFactory)();
        if (!is_object($input)) {
            throw new \RuntimeException('Bootstrap request input factory must return an object.');
        }

        return $input;
    }

    public function bootstrapRuntime(): object
    {
        $runtime = ($this->bootstrapRuntimeFactory)($this);
        if (!is_object($runtime)) {
            throw new \RuntimeException('Bootstrap runtime factory must return an object.');
        }

        return $runtime;
    }

    public function zendAutoloaderLoader(): object
    {
        $loader = ($this->zendAutoloaderLoaderFactory)();
        if (!is_object($loader) || !method_exists($loader, 'load')) {
            throw new \RuntimeException('Bootstrap Zend autoloader loader factory must return an object with load().');
        }

        return $loader;
    }

    public function bootstrapLoaderFileStorage(): object
    {
        $fileStorage = ($this->bootstrapLoaderFileStorageFactory)();
        if (!is_object($fileStorage)) {
            throw new \RuntimeException('Bootstrap loader file storage factory must return an object.');
        }

        return $fileStorage;
    }

    public function createBootstrapObject(string $class, array $arguments): object
    {
        $object = ($this->bootstrapObjectFactory)($class, $arguments);
        if (!is_object($object)) {
            throw new \RuntimeException('Bootstrap object factory must return an object.');
        }

        return $object;
    }

    public function loadPhpArrayFile(string $path, mixed $default = null): mixed
    {
        return ($this->phpArrayFileLoader)($path, $default);
    }

    public function loadBootstrapConfig(?string $configPath = null): void
    {
        ($this->bootstrapConfigLoader)($this, $configPath);
    }

    public function setupBootstrapErrorHandler(callable $handler): void
    {
        ($this->bootstrapErrorHandlerSetup)($this, $handler);
    }

    public function phpRuntimeSettings(): object
    {
        $settings = ($this->phpRuntimeSettingsFactory)();
        if (!is_object($settings)) {
            throw new \RuntimeException('Bootstrap PHP runtime settings factory must return an object.');
        }

        return $settings;
    }

    public function errorHandlerRegistrar(): callable
    {
        return $this->errorHandlerRegistrar;
    }

    public function setupErrorHandler(callable $handler, string $defaultTimezone = 'Europe/Helsinki'): void
    {
        ($this->errorHandlerSetup)($handler, $defaultTimezone);
    }

    public function logError(string $message): void
    {
        ($this->errorLogger)($message, $this->state->logDir());
    }

    /**
     * @return array<string, callable>
     */
    private static function injectedDefaultFactories(?callable $defaultFactoriesFactory): array
    {
        if ($defaultFactoriesFactory === null) {
            throw new \RuntimeException('Bootstrap context default factories factory is not configured.');
        }

        $factories = $defaultFactoriesFactory();
        foreach ([
            'stateFactory',
            'containerFactory',
            'requestInputFactory',
            'bootstrapRuntimeFactory',
            'zendAutoloaderLoaderFactory',
            'bootstrapLoaderFileStorageFactory',
            'bootstrapObjectFactory',
            'bootstrapConfigLoader',
            'bootstrapErrorHandlerSetup',
            'phpRuntimeSettingsFactory',
            'errorHandlerRegistrar',
            'errorHandlerSetup',
            'errorLogger',
        ] as $key) {
            if (!isset($factories[$key]) || !is_callable($factories[$key])) {
                throw new \RuntimeException('Bootstrap context default factories must provide callable ' . $key . '.');
            }
        }

        return $factories;
    }

    private static function createState(callable $stateFactory): state
    {
        $state = $stateFactory();
        if (!$state instanceof state) {
            throw new \RuntimeException('Bootstrap context state factory must return a bootstrap state.');
        }

        return $state;
    }

}
