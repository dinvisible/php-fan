<?php

declare(strict_types=1);

namespace fan\core\exception;
/**
 * Exception a fatal error. This exception must be caught in bootstrap
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
class fatal extends base
{
    public function __construct(string $logErrMsg, string $showErrMsg = '', string $errorFile = '', int $code = E_USER_ERROR, ?\Throwable $previous = null)
    {
        if (!headers_sent()) {
            header('HTTP/1.1 500 Internal Server Error');
        }

        $this->showErrMsg = $showErrMsg;
        if ($errorFile) {
            $this->showErrFile = $errorFile;
        }

        parent::__construct($showErrMsg, $code, $previous);

        $this->_logByPhp('Fatal error (http://' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '') . '). ' . $logErrMsg);
    }
}
