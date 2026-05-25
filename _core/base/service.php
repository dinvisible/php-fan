<?php

declare(strict_types=1);

namespace fan\core\base;
use fan\core\service\service_listener_state;
use fan\core\service\service_single_state;


/**
 * Base abstract service.
 *
 * This file is part PHP-FAN (php-framework from Alexandr Nosov)
 * Copyright (C) 2005-2007 Alexandr Nosov, http://www.alex.4n.com.ua/
 *
 * Licensed under the terms of the GNU Lesser General Public License:
 *     http://www.opensource.org/licenses/lgpl-license.php
 *
 * Do not remove this comment if you want to use script!
 * Не удаляйте данный комментарий, если вы хотите использовать скрипт!
 *
 * @author Alexandr Nosov (alex@4n.com.ua)
 * @version 05.02.008 (15.09.2015)
 */
abstract class service
{
    /**
     * @var \fan\core\service\config\row|null
     */
    protected ?object $config = null;

    /**
     * @var array<string, object>
     */
    protected array $delegate = [];

    /**
     * @var array<string, string[]>
     */
    protected array $delegateRule = [];

    /**
     * DB operation on exception: rollback, commit, nothing, or null.
     *
     * @var string|null
     */
    protected ?string $exceptionDbOper = null;

    /**
     * Error logging strategy on exception: php, service, nothing, or null.
     *
     * @var string|null
     */
    private ?string $exceptionLog = null;

    private ?object $serviceBootstrapRuntime = null;
    private ?object $serviceConfigurator = null;

    /**
     * @var callable|null
     */
    private $serviceCacheFactory = null;
    /**
     * @var callable|null
     */
    private $serviceEngineFactory = null;
    /**
     * @var callable|null
     */
    private $serviceExceptionFactory = null;
    /**
     * @var callable|null
     */
    private $classNameResolver = null;
    /**
     * @var callable|null
     */
    private $arrayValueReader = null;
    private ?service_dependencies $serviceDependencies = null;
    private ?service_listener_state $serviceListenerState = null;
    private ?service_single_state $serviceSingleState = null;

    protected function __construct(
        $allowIni = true,
        ?object $serviceBootstrapRuntime = null,
        ?object $serviceConfigurator = null,
        ?callable $serviceCacheFactory = null,
        ?callable $serviceEngineFactory = null,
        ?callable $serviceExceptionFactory = null,
        ?callable $classNameResolver = null,
        ?callable $arrayValueReader = null
    )
    {
        $this->setServiceDependencies(
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory,
            null,
            null,
            $serviceEngineFactory,
            $serviceExceptionFactory,
            $classNameResolver,
            $arrayValueReader
        );

        if ($allowIni) {
            $this->serviceBootstrapRuntime()->getInitializer()->setServiceParam(get_class($this));
        }

        $this->_saveInstance()
            ->_setConfig()
            ->resetEnabled();
    }

    public static function checkName($name): string
    {
        return substr($name, 0, 8) === 'fan\core'
            ? 'fan\project' . substr($name, 8)
            : $name;
    }

    abstract public function isSingleton(): bool;

    public function setServiceDependencies(
        ?object $serviceBootstrapRuntime = null,
        ?object $serviceConfigurator = null,
        ?callable $serviceCacheFactory = null,
        ?service_listener_state $serviceListenerState = null,
        ?service_single_state $serviceSingleState = null,
        ?callable $serviceEngineFactory = null,
        ?callable $serviceExceptionFactory = null,
        ?callable $classNameResolver = null,
        ?callable $arrayValueReader = null
    ): static
    {
        $dependencies = $serviceBootstrapRuntime instanceof service_dependencies
            ? $serviceBootstrapRuntime
            : service_dependencies::fromLegacy(
                $serviceBootstrapRuntime,
                $serviceConfigurator,
                $serviceCacheFactory,
                $serviceListenerState,
                $serviceSingleState,
                $serviceEngineFactory,
                $serviceExceptionFactory,
                $classNameResolver,
                $arrayValueReader
            );

        $this->applyServiceDependencies($dependencies);

        return $this;
    }

    private function applyServiceDependencies(service_dependencies $dependencies): void
    {
        $this->serviceDependencies = $dependencies;
        $this->serviceBootstrapRuntime = $dependencies->serviceBootstrapRuntime();
        $this->serviceConfigurator = $dependencies->serviceConfigurator();
        $this->serviceCacheFactory = $dependencies->serviceCacheFactory();
        $this->serviceEngineFactory = $dependencies->serviceEngineFactory();
        $this->serviceExceptionFactory = $dependencies->serviceExceptionFactory();
        $this->classNameResolver = $dependencies->classNameResolver();
        $this->arrayValueReader = $dependencies->arrayValueReader();
        $this->serviceListenerState = $dependencies->serviceListenerState();
        $this->serviceSingleState = $dependencies->serviceSingleState();
    }

    public function isEnabled(): bool
    {
        return (bool)$this->getConfig('ENABLED', true);
    }

    public function resetEnabled(): static
    {
        $this->_getConfigurator()->reset($this->serviceClassName(), 'ENABLED');

        return $this;
    }

    public function getConfig($key = null, $default = null): mixed
    {
        if (null === $key || null === $this->config) {
            return $this->config;
        }

        return $this->config->get($key, $default);
    }

    public function setExceptionDbOper(?string $exceptionDbOper = null): self
    {
        if (
            null === $exceptionDbOper ||
            in_array($exceptionDbOper, ['rollback', 'commit', 'nothing'], true)
        ) {
            $this->exceptionDbOper = $exceptionDbOper;
        }

        return $this;
    }

    public function getExceptionDbOper(): ?string
    {
        return $this->exceptionDbOper;
    }

    public function getExceptionLogType(): string
    {
        return null === $this->exceptionLog ? 'service' : $this->exceptionLog;
    }

    public function setExceptionLogType($exceptionLog): self
    {
        if (
            null === $exceptionLog ||
            in_array($exceptionLog, ['php', 'service', 'nothing'], true)
        ) {
            $this->exceptionLog = $exceptionLog;
        }

        return $this;
    }

    /**
     * @param callable $callBack Callable invoked to complete the delegated operation.
     */
    public function addListener(string $eventName, callable $callBack): self
    {
        $this->_subscribeForService($this->serviceClassName(), $eventName, $callBack);

        return $this;
    }

    protected function _saveInstance(): static
    {
        return $this;
    }

    protected function _singleState(): service_single_state
    {
        return $this->serviceSingleState ?? throw new \RuntimeException(
            'Service single state is not configured for ' . get_class($this) . '.'
        );
    }

    protected function _setConfig(): static
    {
        $this->config = $this->_getConfigurator()->getServiceConfig($this);

        return $this;
    }

    protected function _getCacheData(string $key, mixed $default = null): mixed
    {
        $cache = $this->serviceCache();
        /* @var $cache \fan\core\service\cache */
        $data = $cache->get($this->serviceClassName(), []);

        return $this->arrayValueReader()($data, $key, $default);
    }

    protected function _setCacheData(string $key, mixed $value): self
    {
        $cache = $this->serviceCache();
        /* @var $cache \fan\core\service\cache */
        $name = $this->serviceClassName();
        $data = $cache->get($name, []);
        $data[$key] = $value;
        $cache->set($name, $data);

        return $this;
    }

    protected function _getConfigurator(): object
    {
        return $this->serviceConfigurator();
    }

    protected function _getEngine($name, $object = true): mixed
    {
        $class = get_class($this) . '\\' . $name;

        if (substr($class, 0, 9) === 'fan\core\\') {
            $class = 'fan\project\\' . substr($class, 9);
        }

        if (!$this->serviceBootstrapRuntime()->loadClass($class, true)) {
            return null;
        }

        $class = '\\' . $class;
        if (!$object) {
            return $class;
        }

        $object = ($this->serviceEngineFactory())($class);
        if (!is_object($object)) {
            throw new \UnexpectedValueException('Service engine factory must return an object.');
        }
        if (method_exists($object, 'setFacade')) {
            $object->setFacade($this);
        }

        return $object;
    }

    /**
     * @throws \fan\core\exception\service\fatal
     */
    protected function _getDelegate(mixed $class): mixed
    {
        if (!empty($this->delegate[$class])) {
            return $this->delegate[$class];
        }

        $this->delegate[$class] = $this->_getEngine('delegate\\' . $class);
        if (empty($this->delegate[$class])) {
            throw $this->createServiceFatalException('Delegate service class "' . $class . '" isn\'t found!');
        }

        return $this->delegate[$class];
    }

    protected function _extensionCall(string $method, array $args): bool
    {
        return false;
    }

    /**
     * @throws \fan\core\exception\service\fatal
     */
    protected function _makeServiceException(
        string $logErrMsg,
        ?string $exceptionDbOper = 'rollback',
        int $code = E_USER_ERROR,
        ?\Exception $previous = null
    ): void {
        if (null === $this->exceptionDbOper) {
            $this->exceptionDbOper = $exceptionDbOper;
        }

        throw $this->createServiceFatalException($logErrMsg, $code, $previous);
    }

    /**
     * @param callable $callBack Callable invoked to complete the delegated operation.
     *
     * @throws \fan\core\exception\service\fatal
     */
    protected function _subscribeForService(string $serviceName, string $eventName, callable $callBack): void
    {
        if (!is_callable($callBack)) {
            throw $this->createServiceFatalException('Incorrect callback-function for subscribing.');
        }

        $this->serviceListenerState()->subscribe($serviceName, $eventName, $callBack);
    }

    protected function _broadcastMessage(string $eventName, mixed $data): void
    {
        $serviceName = $this->serviceClassName();

        foreach ($this->serviceListenerState()->listenersFor($serviceName, $eventName) as $callBack) {
            $callBack($data);
        }
    }

    protected function serviceBootstrapRuntime(): object
    {
        if ($this->serviceBootstrapRuntime !== null) {
            return $this->serviceBootstrapRuntime;
        }

        throw new \RuntimeException('Bootstrap runtime service is not configured for ' . get_class($this) . '.');
    }

    protected function serviceConfigurator(): object
    {
        if ($this->serviceConfigurator !== null) {
            return $this->serviceConfigurator;
        }

        throw new \RuntimeException('Config service is not configured for ' . get_class($this) . '.');
    }

    protected function serviceCache(): object
    {
        if ($this->serviceCacheFactory !== null) {
            return ($this->serviceCacheFactory)('service_data');
        }

        throw new \RuntimeException('Service data cache factory is not configured for ' . get_class($this) . '.');
    }

    protected function serviceEngineFactory(): callable
    {
        if ($this->serviceEngineFactory !== null) {
            return $this->serviceEngineFactory;
        }

        throw new \RuntimeException('Service engine factory is not configured for ' . get_class($this) . '.');
    }

    protected function serviceClassName(): string
    {
        if (!is_callable($this->classNameResolver)) {
            throw new \RuntimeException('Class name resolver is not configured for ' . get_class($this) . '.');
        }

        $className = ($this->classNameResolver)($this);
        if (!is_string($className) || $className === '') {
            throw new \UnexpectedValueException('Class name resolver must return a non-empty string.');
        }

        return $className;
    }

    protected function arrayValueReader(): callable
    {
        if (is_callable($this->arrayValueReader)) {
            return $this->arrayValueReader;
        }

        throw new \RuntimeException('Array value reader is not configured for ' . get_class($this) . '.');
    }

    protected function createServiceFatalException(string $message, int $code = E_USER_ERROR, ?\Throwable $previous = null): \Throwable
    {
        if (!is_callable($this->serviceExceptionFactory)) {
            throw new \RuntimeException('Service exception factory is not configured for ' . get_class($this) . '.');
        }

        $exception = ($this->serviceExceptionFactory)(
            '\fan\project\exception\service\fatal',
            $this,
            $message,
            $code,
            $previous
        );
        if (!$exception instanceof \Throwable) {
            throw new \UnexpectedValueException('Service exception factory must return a throwable object.');
        }

        return $exception;
    }

    protected function serviceListenerState(): service_listener_state
    {
        return $this->serviceListenerState ?? throw new \RuntimeException(
            'Service listener state is not configured for ' . get_class($this) . '.'
        );
    }

    /**
     * @throws \fan\core\exception\service\fatal
     */
    public function __call(string $method, array $args): mixed
    {
        foreach ($this->delegateRule as $class => $methods) {
            if (!in_array($method, $methods, true)) {
                continue;
            }

            $delegate = $this->_getDelegate($class);

            return null === $delegate
                ? null
                : $delegate->{$method}(...(empty($args) ? [] : $args));
        }

        if (!$this->_extensionCall($method, $args)) {
            throw $this->createServiceFatalException('Incorrect call of service - unknown method "' . $method . '"!');
        }

        return null;
    }
}
