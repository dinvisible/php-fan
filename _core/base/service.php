<?php

declare(strict_types=1);

namespace fan\core\base;

use fan\core\di\container_interface;
use fan\project\exception\service\fatal as fatalException;

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
    use \fan\core\di\container_aware_trait;

    /**
     * @var array<string, array<string, callable[]>>
     */
    private static array $listeners = [];

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

    protected function __construct($allowIni = true, ?container_interface $serviceContainer = null)
    {
        $this->setServiceContainer($serviceContainer);

        if ($allowIni) {
            \bootstrap::getInitializer()->setServiceParam(get_class($this));
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

    public function getContainerService(string $serviceName, mixed ...$arguments): mixed
    {
        return $this->containerService($serviceName, ...$arguments);
    }

    public function isEnabled(): bool
    {
        return (bool)$this->getConfig('ENABLED', true);
    }

    public function resetEnabled(): static
    {
        $this->_getConfigurator()->reset(get_class_name($this), 'ENABLED');

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
        $this->_subscribeForService(get_class_name($this), $eventName, $callBack);

        return $this;
    }

    protected function _saveInstance(): static
    {
        return $this;
    }

    protected function _setConfig(): static
    {
        $this->config = $this->_getConfigurator()->getServiceConfig($this);

        return $this;
    }

    protected function _getCacheData(string $key, mixed $default = null): mixed
    {
        $cache = $this->containerService('cache', 'service_data');
        /* @var $cache \fan\core\service\cache */
        $data = $cache->get(get_class_name($this), []);

        return array_val($data, $key, $default);
    }

    protected function _setCacheData(string $key, mixed $value): self
    {
        $cache = $this->containerService('cache', 'service_data');
        /* @var $cache \fan\core\service\cache */
        $name = get_class_name($this);
        $data = $cache->get($name, []);
        $data[$key] = $value;
        $cache->set($name, $data);

        return $this;
    }

    protected function _getConfigurator(): object
    {
        return $this->containerService('config', 'service');
    }

    protected function _getEngine($name, $object = true): mixed
    {
        $class = get_class($this) . '\\' . $name;

        if (substr($class, 0, 9) === 'fan\core\\') {
            $class = 'fan\project\\' . substr($class, 9);
        }

        if (!\bootstrap::loadClass($class, true)) {
            return null;
        }

        $class = '\\' . $class;
        if (!$object) {
            return $class;
        }

        $object = new $class();
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
            throw new fatalException($this, 'Delegate service class "' . $class . '" isn\'t found!');
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

        throw new fatalException($this, $logErrMsg, $code, $previous);
    }

    /**
     * @param callable $callBack Callable invoked to complete the delegated operation.
     *
     * @throws \fan\core\exception\service\fatal
     */
    protected function _subscribeForService(string $serviceName, string $eventName, callable $callBack): void
    {
        if (!is_callable($callBack)) {
            throw new fatalException($this, 'Incorrect callback-function for subscribing.');
        }

        if (!isset(self::$listeners[$serviceName][$eventName])) {
            self::$listeners[$serviceName][$eventName] = [];
        }

        self::$listeners[$serviceName][$eventName][] = $callBack;
    }

    protected function _broadcastMessage(string $eventName, mixed $data): void
    {
        $serviceName = get_class_name($this);

        if (!isset(self::$listeners[$serviceName][$eventName])) {
            return;
        }

        foreach (self::$listeners[$serviceName][$eventName] as $callBack) {
            call_user_func($callBack, $data);
        }
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
                : call_user_func_array([$delegate, $method], empty($args) ? [] : $args);
        }

        if (!$this->_extensionCall($method, $args)) {
            throw new fatalException($this, 'Incorrect call of service - unknown method "' . $method . '"!');
        }
    }
}
