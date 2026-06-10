<?php

declare(strict_types=1);

namespace fan\core\exception\plain;
use fan\core\exception\base;

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
class fatal extends base
{

    /**
     * Instance of class maked exception
     * @var object
     */
    protected ?object $controller = null;

    public function __construct(
        object $controller,
        string $logMessage,
        int $code = E_USER_ERROR,
        ?\Throwable $previous = null,
        ?object $exceptionDatabaseConnections = null,
        ?object $exceptionRuntimeLogger = null,
        ?object $exceptionRequestService = null,
        ?object $exceptionErrorService = null,
        ?object $exceptionHeaderWriter = null,
        ?callable $classNameResolver = null
    )
    {
        $this->controller = $controller;
        $classNameResolver = \Closure::fromCallable(
            $classNameResolver ?? static fn(object $object): string => get_class($object)
        );

        parent::__construct($logMessage, $code, $previous, $exceptionDatabaseConnections, $exceptionRuntimeLogger, $exceptionRequestService, $exceptionErrorService, $exceptionHeaderWriter);

        $this->_logByService('Plain controller fatal error (' . $this->className($controller, $classNameResolver) . '). ' . $logMessage);
    }

    public function getController(): object
    {
        return $this->controller;
    }

    private function className(object $object, \Closure $classNameResolver): string
    {
        return $classNameResolver($object);
    }

}
