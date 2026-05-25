<?php
declare(strict_types=1);

namespace fan\core\service;
use fan\core\base\service\single;


/**
 * Class of plain handler
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
 * @author: Alexandr Nosov (alex@4n.com.ua)
 * @version of file: 05.02.008 (15.09.2015)
 */
class plain extends single
{

    /**
     * Instance of matcher
     * @var \fan\core\service\matcher
     */
    protected ?object $matcher = null;

    /**
     * Used Application Names
     * @var \fan\core\service\plain\base
     */
    protected ?object $controller = null;

    /**
     * @var numeric - error code
     */
    protected int|float|null $errCode = null;

    protected ?string $errMsg = null;

    /**
     * Lict of headers
     * @var array
     */
    protected array $headers = [
        'response'    => 200,
        'contentType' => null,
        'encoding'    => null,
        'filename'    => null,
        'disposition' => true,
        'length'      => null,
        'legthRange'  => 'bytes',
        'modified'    => null,
        'cacheLimit'  => 0,
    ];

    protected mixed $plainConfigFactory = null;

    protected ?object $header = null;

    protected mixed $controllerDependenciesFactory = null;

    protected mixed $controllerFactory = null;

    public function __construct(
        bool $allowIni = true,
        ?object $matcher = null,
        ?callable $plainConfigFactory = null,
        ?object $header = null,
        ?callable $controllerDependenciesFactory = null,
        ?callable $controllerFactory = null,
        ?object $serviceBootstrapRuntime = null,
        ?object $serviceConfigurator = null,
        ?callable $serviceCacheFactory = null
    )
    {
        $this->matcher = $matcher;
        $this->plainConfigFactory = $plainConfigFactory;
        $this->header = $header;
        $this->controllerDependenciesFactory = $controllerDependenciesFactory;
        $this->controllerFactory = $controllerFactory;
        parent::__construct($allowIni, $serviceBootstrapRuntime, $serviceConfigurator, $serviceCacheFactory);
    }

    // ======== Main Interface methods ======== \\

    public function handleContent(int|string $key, string $controllerClass, string $method): mixed
    {
        return $this->_setController($key, $controllerClass)->_getFinalContent($method);
    }

    public function getHandleData(): array
    {
        return $this->matcher->getCurrentItem()->handler->toArray();
    }

    /**
     * @param string $request Request object or payload handled by the operation.
     */
    public function transfer(string $request, ?string $host = null, bool $shiftCurrent = true): mixed
    {
        $this->matcher->setUri($request, $host, $shiftCurrent);
        $handler = $this->matcher->getCurrentHandler(true)->toArray();
        $method = $handler['method'];

        return $method(...(empty($handler['param']) ? [] : $handler['param']));
    }

    /**
     * @param string $value Value that should be applied or transformed.
     *
     * @throws \fan\project\exception\service\fatal
     */
    public function addHeader(string $key, mixed $value): static
    {
        if (!array_key_exists($key, $this->headers)) {
            throw $this->createServiceFatalException('Unknown header key "' . $key . '"');
        }
        $this->headers[$key] = $value;
        return $this;
    }

    public function setHeaders(array $headers): static
    {
        foreach ($headers as $k => $v) {
            $this->addHeader($k, $v);
        }
        return $this;
    }

    /**
     * @throws \fan\project\exception\service\fatal
     */
    public function setErrorMessage(string $errMsg, int|float $errCode = 404): static
    {
        if (is_numeric($errCode) && $errCode >= 400 && $errCode <= 599) {
            $this->errCode = $errCode;
        } else {
            throw $this->createServiceFatalException('Error code has incorrect value "' . $errCode . '". It must be number between 400 and 599');
        }

        if (!empty($errMsg)) {
            $this->errMsg = $errMsg;
        }

        return $this;
    }

    public function isError(): bool
    {
        return !empty($this->errMsg);
    }

    // ======== Private/Protected methods ======== \\

    /**
     * @throws \fan\project\exception\service\fatal
     */
    protected function _getFinalContent(string $method): mixed
    {
        if (empty($this->controller)) {
            throw $this->createServiceFatalException('Engine for plain content isn\'t set.');
        }
        $result = $this->controller->$method();
        if ($this->isError()) {
            $this->_defineError404();
            $result = $this->errMsg;
        }
        $this->_assignHeaders();
        return $result;
    }

    /**
     * @throws \fan\project\exception\service\fatal
     */
    protected function _setController(int|string $controllerKey, string $controllerClass): static
    {
        if (!class_exists($controllerClass)) {
            throw $this->createServiceFatalException('Can\'t find class "' . $controllerClass . '" for plain content.');
        }
        $this->controller = $this->createController(
            $controllerClass,
            $controllerKey,
            $this->controllerDependencies($controllerClass, $controllerKey)
        );
        if (method_exists($this->controller, 'setConfig')) {
            $config = $this->plainConfig()->getControllerConfig($this->controller, $controllerKey);
            $this->controller->setConfig($config);
        }
        return $this;
    }

    protected function controllerDependencies(string $controllerClass, int|string $controllerKey): array
    {
        if (!is_callable($this->controllerDependenciesFactory)) {
            return [];
        }

        $dependencies = ($this->controllerDependenciesFactory)($controllerClass, $controllerKey, $this);

        return is_array($dependencies) ? $dependencies : [];
    }

    protected function createController(string $controllerClass, int|string $controllerKey, array $dependencies): object
    {
        if (!is_callable($this->controllerFactory)) {
            throw new \RuntimeException('Plain controller factory is not configured for plain service.');
        }

        $controller = ($this->controllerFactory)($controllerClass, $this, $controllerKey, $dependencies);
        if (!is_object($controller)) {
            throw new \UnexpectedValueException('Plain controller factory must return an object.');
        }

        return $controller;
    }

    protected function _assignHeaders(): static
    {
        $this->header()->setHeaders($this->headers);
        return $this;
    }

    private function plainConfig(): object
    {
        if (!is_callable($this->plainConfigFactory)) {
            throw new \RuntimeException('Plain config service factory is not configured for plain service.');
        }

        return ($this->plainConfigFactory)();
    }

    private function header(): object
    {
        if ($this->header !== null) {
            return $this->header;
        }

        throw new \RuntimeException('Header service is not configured for plain service.');
    }

    protected function _defineError404(): static
    {
        $this->headers = [
            'response'    => 404,
            'contentType' => 'text/plain',
            'encoding'    => 'charset=utf-8',
            'filename'    => 'Error 404',
            'disposition' => true,
            'length'      => strlen($this->errMsg),
            'legthRange'  => 'bytes',
            'modified'    => null,
            'cacheLimit'  => 0,
        ];
        return $this;
    }

    // ======== The magic methods ======== \\

    // ======== Required Interface methods ======== \\

}
