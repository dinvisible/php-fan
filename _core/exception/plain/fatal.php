<?php

declare(strict_types=1);

namespace fan\core\exception\plain;
/**
 * Exception a plain controller fatal error
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
 * @version of file: 05.02.011 (03.10.2015)
 */
class fatal extends \fan\core\exception\base
{

    /**
     * Instance of class maked exception
     * @var object
     */
    protected ?object $controller = null;

    public function __construct(object $controller, string $logMessage, int $code = E_USER_ERROR, ?\Throwable $previous = null)
    {
        /*
        if (!headers_sent()) {
            header('HTTP/1.1 500 Internal Server Error');
        }
         */
        $this->controller = $controller;

        parent::__construct($logMessage, $code, $previous);

        $this->_logByService('Plain controller fatal error (' . get_class_alt($controller) . '). ' . $logMessage);
    }

    public function getController(): object
    {
        return $this->controller;
    }

}
