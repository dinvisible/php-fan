<?php

declare(strict_types=1);

namespace fan\core\service\database;
if (!defined('MYSQL_ASSOC')) {
    define('MYSQL_ASSOC', defined('MYSQLI_ASSOC') ? MYSQLI_ASSOC : 1);
}
if (!defined('MYSQL_NUM')) {
    define('MYSQL_NUM', defined('MYSQLI_NUM') ? MYSQLI_NUM : 2);
}
if (!defined('MYSQL_BOTH')) {
    define('MYSQL_BOTH', defined('MYSQLI_BOTH') ? MYSQLI_BOTH : 3);
}
/**
 * Description of base
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
abstract class base
{
    /**
     * Facade of service
     * @var fan\core\base\service
     */
    protected ?object $facade = null;

    protected int $resultType = MYSQL_ASSOC;

    /**
     * Connection Parameters
     * @var string
     */
    protected ?array $param = null;

    protected ?array $errorData = null;

    public function __construct(\fan\core\service\database $facade, array $param)
    {
        $this->facade = $facade;
        $this->param  = $param;
        $this->setResultTypes();
    }

    public function setFacade(\fan\core\base\service $facade): static
    {
        if (empty($this->facade)) {
            $this->facade = $facade;
        }
        return $this;
    }

    public function setResultTypes(int|string $resultType = MYSQL_ASSOC): static
    {
        if ($this->_isValidType($resultType)) {
            $this->resultType = (int)$resultType;
        } // ToDo: Maybe exception there if point incorrect type
        return $this;
    }

    abstract public function reconnect(array $param, bool $makeException = true): mixed;

    public function getErrorData(): ?array
    {
        return $this->errorData;
    }

    public function resetError(): static
    {
        $this->errorData = null;
        return $this;
    }

    protected function _isValidType(int|string $resultType): bool
    {
        $validTypes = [
            MYSQL_ASSOC,
            MYSQL_NUM,
            MYSQL_BOTH
        ];
        return in_array((int)$resultType, $validTypes, true);
    }

    protected function _fixError(int|float $operCode, string $operMessage, int|float $errorCode, string $errorMessage, bool $makeException = false): void
    {
        $this->errorData = [
            'oper_code' => $operCode,
            'oper_msg'  => $operMessage,
            'err_code'  => $errorCode,
            'err_msg'   => iconv('', 'UTF-8', $errorMessage),
            'sql'       => $this->parsedSql,
        ];
        $this->facade->fixError($this, $makeException);
    }
}
