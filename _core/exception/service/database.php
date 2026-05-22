<?php

declare(strict_types=1);

namespace fan\core\exception\service;
/**
 * Exception an error 500
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
class database extends fatal
{
    /**
     * @var numeric Code of Service database Operation
     */
    protected int|float|null $operCode = null;
    protected ?string $operMessage = null;

    protected int|float|null $errorNum = null;

    protected ?string $errorMessage = null;

    protected ?string $parsedSql = null;

    public function __construct(\fan\core\service\database $database, int|float|null $operCode, string $operMessage, int|float|null $errorCode, string $errorMessage, ?string $parsedSql)
    {
        $this->operCode    = $operCode;
        $this->operMessage = $operMessage;
        $this->errorNum    = $errorCode;
        $this->showErrMsg  = $errorMessage;
        $this->parsedSql   = $parsedSql;

        $logErrMsg = $operMessage . "\n" . trim($errorMessage) . (empty($errorCode) ? '' : "\nError No: " . $errorCode . '.');
        parent::__construct($database, $logErrMsg, E_USER_WARNING, null);
    }

    public function getOperationCode(): int|float|null
    {
        return $this->operCode;
    }

    public function getOperation(): ?string
    {
        return $this->operMessage;
    }

    public function getErrorNum(): int|float|null
    {
        return $this->errorNum;
    }

    public function getParsedSql(): ?string
    {
        return $this->parsedSql;
    }


    protected function _logErrorMessage(string $logType): static
    {
        if ($this->operCode < 3) {
            parent::_logErrorMessage($logType);
        } elseif ((string)$logType !== 'nothing') {
            $this->containerService('error')->logDatabaseError(
                    $this->service->getConnectionName(),
                    $this->operMessage,
                    $this->showErrMsg,
                    $this->errorNum,
                    $this->parsedSql
            );
        }
        return $this;
    }

}
