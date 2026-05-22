<?php
declare(strict_types=1);

namespace fan\core\service;
use fan\project\exception\service\fatal as fatalException;
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
class plain extends \fan\core\base\service\single
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

    protected function __construct(bool $allowIni = true)
    {
        parent::__construct($allowIni);
        $this->matcher = \fan\project\service\matcher::instance();
    }

    // ======== Static methods ======== \\

    public static function getContent(int|string $key, string $controllerClass, string $method): mixed
    {
        $instance = \fan\project\service\plain::instance();
        /* @var $instance \fan\core\service\plain */
        return $instance->_setController($key, $controllerClass)->_getFinalContent($method);
    }

    // ======== Main Interface methods ======== \\

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
        return call_user_func_array($handler['method'], empty($handler['param']) ? [] : $handler['param']);
    }

    /**
     * @param string $value Value that should be applied or transformed.
     *
     * @throws fatalException
     */
    public function addHeader(string $key, mixed $value): static
    {
        if (!array_key_exists($key, $this->headers)) {
            throw new fatalException($this, 'Unknown header key "' . $key . '"');
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
     * @throws fatalException
     */
    public function setErrorMessage(string $errMsg, int|float $errCode = 404): static
    {
        if (is_numeric($errCode) && $errCode >= 400 && $errCode <= 599) {
            $this->errCode = $errCode;
        } else {
            throw new fatalException($this, 'Error code has incorrect value "' . $errCode . '". It must be number between 400 and 599');
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
     * @throws fatalException
     */
    protected function _getFinalContent(string $method): mixed
    {
        if (empty($this->controller)) {
            throw new fatalException($this, 'Engine for plain content isn\'t set.');
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
     * @throws fatalException
     */
    protected function _setController(int|string $controllerKey, string $controllerClass): static
    {
        if (!class_exists($controllerClass)) {
            throw new fatalException($this, 'Can\'t find class "' . $controllerClass . '" for plain content.');
        }
        $this->controller = new $controllerClass($this, $controllerKey);
        if (method_exists($this->controller, 'setConfig')) {
            $config = \fan\core\service\config::instance('plain')->getControllerConfig($this->controller, $controllerKey);
            $this->controller->setConfig($config);
        }
        return $this;
    }

    protected function _assignHeaders(): static
    {
        $this->containerService('header')->setHeaders($this->headers);
        return $this;
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
