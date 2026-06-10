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
    public function __construct(
        string $logErrMsg,
        string $showErrMsg = '',
        string $errorFile = '',
        int $code = E_USER_ERROR,
        ?\Throwable $previous = null,
        ?object $requestInput = null,
        ?object $exceptionDatabaseConnections = null,
        ?object $exceptionRuntimeLogger = null,
        ?object $exceptionRequestService = null,
        ?object $exceptionErrorService = null,
        ?callable $requestInputFactory = null,
        ?object $exceptionHeaderWriter = null
    )
    {
        $this->setExceptionDependencies($exceptionDatabaseConnections, $exceptionRuntimeLogger, $exceptionRequestService, $exceptionErrorService, $exceptionHeaderWriter);
        $this->sendInternalServerErrorHeader();

        $this->showErrMsg = $showErrMsg;
        if ($errorFile) {
            $this->showErrFile = $errorFile;
        }

        parent::__construct($showErrMsg, $code, $previous, $exceptionDatabaseConnections, $exceptionRuntimeLogger, $exceptionRequestService, $exceptionErrorService, $exceptionHeaderWriter);

        $input = $requestInput ?? $this->createDefaultRequestInput($requestInputFactory);
        $host = (string)$input->serverValue('HTTP_HOST', '');
        $requestUri = (string)$input->serverValue('REQUEST_URI', '');
        $this->_logByPhp('Fatal error (http://' . $host . $requestUri . '). ' . $logErrMsg);
    }

    private function createDefaultRequestInput(?callable $requestInputFactory): object
    {
        if ($requestInputFactory !== null) {
            $requestInput = $requestInputFactory();
            if (!is_object($requestInput)) {
                throw new \RuntimeException('Fatal exception request input factory must return an object.');
            }

            return $requestInput;
        }

        throw new \RuntimeException('Fatal exception request input dependency is not configured.');
    }
}
