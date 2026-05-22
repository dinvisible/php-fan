<?php

declare(strict_types=1);

namespace fan\core\exception\service;
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
class fatal extends \fan\core\exception\base
{

    /**
     * @var \fan\core\base\service Instance of class maked exception
     */
    protected ?object $service = null;

    public function __construct(\fan\core\base\service $service, string $logErrMsg, int $code = E_USER_ERROR, ?\Throwable $previous = null)
    {
        $this->service = $service;

        parent::__construct($logErrMsg, $code, $previous);

        $this->_logErrorMessage($service->getExceptionLogType());
    }

    public function getService(): \fan\core\base\service
    {
        return $this->service;
    }

    protected function _logErrorMessage(string $logType): static
    {
        if (in_array($logType, ['php', 'service'])) {
            $logMethod = $logType === 'php' ? '_logByPhp' : '_logByService';
            $this->$logMethod('Service fatal error (' . get_class($this->service) . '). ' . $this->logErrMsg);
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
