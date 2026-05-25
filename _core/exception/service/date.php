<?php

declare(strict_types=1);

namespace fan\core\exception\service;
use fan\core\exception\base;

/**
 * Exception a service fatal error
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
 * @version of file: 05.02.009 (23.09.2015)
 */
class date extends base
{

    public function __construct(
        string $logErrMsg,
        int $code = E_USER_ERROR,
        ?\Throwable $previous = null,
        ?object $exceptionDatabaseConnections = null,
        ?object $exceptionRuntimeLogger = null,
        ?object $exceptionRequestService = null,
        ?object $exceptionErrorService = null
    )
    {
        parent::__construct($logErrMsg, $code, $previous, $exceptionDatabaseConnections, $exceptionRuntimeLogger, $exceptionRequestService, $exceptionErrorService);
        $this->_logByPhp($logErrMsg);
    }
}
