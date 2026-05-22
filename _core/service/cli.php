<?php
declare(strict_types=1);

namespace fan\core\service;
use fan\project\exception\service\fatal as fatalException;
/**
 * Class of cli handler
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
 * @version of file: 05.02.001 (10.03.2014)
 */
class cli extends \fan\core\base\service\single
{
    protected function __construct(bool $allowIni = true)
    {
        parent::__construct($allowIni);
        $this->matcher = \fan\project\service\matcher::instance();
    }

    // ======== Static methods ======== \\

    public static function getContent(string $controllerClass, string $method): mixed
    {
        $instance = \fan\project\service\cli::instance();
        return $instance->_setController($controllerClass)->_getFinalContent($method);
    }

    // ======== Main Interface methods ======== \\

    // ======== Private/Protected methods ======== \\

    /**
     * @throws fatalException
     */
    protected function _getFinalContent(string $method): mixed
    {
        if (empty($this->controller)) {
            throw new fatalException($this, 'Engine for CLI content isn\'t set.');
        }
        if (!method_exists($this->controller, $method)) {
            throw new fatalException($this, 'Engine of CLI content don\'t have method "' . $method . '".');
        }
        $result = $this->controller->$method();
        return $result;
    }

    /**
     * @throws fatalException
     */
    protected function _setController(string $controller): static
    {
        $controller = ltrim($controller, '\\');
        $controller = (substr($controller, 0, 12) === 'fan\project\\' ? '\\' : '\fan\project\cli\\') . $controller;
        if (!class_exists($controller)) {
            throw new fatalException($this, 'Can\'t find class "' . $controller . '" for CLI content.');
        }
        $this->controller = new $controller($this);
        if (method_exists($this->controller, 'setConfig')) {
            $config = \fan\core\service\config::instance('cli')->getControllerConfig($this->controller);
            $this->controller->setConfig($config);
        }
        return $this;
    }

    // ======== The magic methods ======== \\

    // ======== Required Interface methods ======== \\

}
