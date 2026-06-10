<?php

declare(strict_types=1);

namespace fan\core\exception\service;
use fan\core\base\service;
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
 * @version of file: 05.02.011 (03.10.2015)
 */
class fatal extends base
{

    /**
     * @var \fan\core\base\service Instance of class maked exception
     */
    protected ?object $service = null;

    public function __construct(
        service $service,
        string $logErrMsg,
        int $code = E_USER_ERROR,
        ?\Throwable $previous = null,
        ?object $exceptionDatabaseConnections = null,
        ?object $exceptionRuntimeLogger = null,
        ?object $exceptionRequestService = null,
        ?object $exceptionErrorService = null
    )
    {
        $this->service = $service;

        parent::__construct($logErrMsg, $code, $previous, $exceptionDatabaseConnections, $exceptionRuntimeLogger, $exceptionRequestService, $exceptionErrorService);

        $this->_logErrorMessage($service->getExceptionLogType());
    }

    public function getService(): service
    {
        return $this->service;
    }

    protected function _logErrorMessage(string $logType): static
    {
        if (in_array($logType, ['php', 'service'])) {
            $logMethod = $logType === 'php' ? '_logByPhp' : '_logByService';
            try {
                $this->$logMethod('Service fatal error (' . get_class($this->service) . '). ' . $this->logErrMsg);
            } catch (\RuntimeException $exception) {
                if (!str_starts_with($exception->getMessage(), 'Exception ') || !str_contains($exception->getMessage(), ' dependency is not configured.')) {
                    throw $exception;
                }
            }
        }
        return $this;
    }

    protected function _defineDbOper(?string $dbOper = null): ?string
    {
        if (empty($dbOper) && method_exists($this->service, 'getExceptionDbOper')) {
            $dbOper = $this->service->getExceptionDbOper();
        }
        return parent::_defineDbOper($dbOper);
    }

}
