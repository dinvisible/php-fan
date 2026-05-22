<?php

declare(strict_types=1);

namespace fan\core\service\database;
/**
 * ADOdb wrapper for template engine
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
 * @version of file: 05.02.004 (25.12.2014)
 */
class adodb extends base
{

    private ?object $conn = null;

    private ?string $errorMsg = null;

    public function __construct(\fan\core\base\service $facade, \fan\core\service\config\base $config)
    {
        parent::__construct($facade, $config, false);
        \fan\project\adapter\adodb::defineErrorHandler();

        $this->conn = \fan\project\adapter\adodb::newConnection((string)$config['DRIVER']);
        $this->reconnect($config->toArray());
        $this->_handleSql();
    }

    public function qstr(string $s): string
    {
        return $this->conn->qstr($s);
    }

    public function start_transaction(): void
    {
        $this->conn->BeginTrans();
    }

    public function commit(): void
    {
        $this->conn->CommitTrans();
    }

    public function rollback(): void
    {
        $this->conn->RollbackTrans();
    }

    public function connectionClose(): void
    {
        $this->conn->close();
    }

    public function reconnect(array $config, bool $makeException = true): mixed
    {
        $this->conn->NConnect((string)$config['HOST'], (string)$config['USER'], (string)$config['PASSWORD'], (string)$config['DATABASE']);
    }

    /**
     * @param ?array $params Parameter set passed into the operation.
     */
    public function execute(string $sql, ?array $params = null): mixed
    {
        return $this->_handleSql("Execute", $sql, $params, false);
    }

    public function getInsertId(): mixed
    {
        $cResult = $this->conn->Insert_ID();
        if ($this->_handleSql()) {
            return $cResult;
        }
        return false;
    }

    /**
     * @param ?array $params Parameter set passed into the operation.
     */
    public function getOne(string $sql, ?array $params = null): mixed
    {
        return $this->_handleSql("GetOne", $sql, $params, false);
    }

    /**
     * @param ?array $params Parameter set passed into the operation.
     */
    public function getRow(string $sql, ?array $params = null): mixed
    {
        return $this->_handleSql("GetRow", $sql, $params);
    }

    /**
     * @param ?array $params Parameter set passed into the operation.
     */
    public function getRowAssoc(string $sql, ?array $params = null): array
    {
        $rs = $this->conn->Execute($sql, $params);
        if ($rs && !$rs->EOF) {
            $cResult = $rs->GetRowAssoc(false);
            if ($this->_handleSql(null, $sql)) {
                return $cResult;
            }
        } // check result set
        return [];
    }

    /**
     * @param ?array $params Parameter set passed into the operation.
     */
    public function getCol(string $sql, ?array $params = null): mixed
    {
        return $this->_handleSql("GetCol", $sql, $params);
    }

    /**
     * @param ?array $params Parameter set passed into the operation.
     */
    public function getAssoc(string $sql, ?array $params = null): mixed
    {
        return $this->_handleSql("GetAssoc", $sql, $params);
    }

    /**
     * @param ?array $params Parameter set passed into the operation.
     */
    public function getAll(string $sql, ?array $params = null): mixed
    {
        return $this->_handleSql("GetAll", $sql, $params);
    }

    /**
     * @param ?array $params Parameter set passed into the operation.
     */
    public function getAllLimit(string $sql, ?array $params = null, int|float $qtt = -1, int|float $offset = -1): array
    {
        $cResult = $this->conn->SelectLimit($sql, (int)$qtt, (int)$offset, $params);
        $this->errorData = $this->conn->ErrorMsg();
        if ($this->errorData || !$cResult) {
            if (!$this->errorData) {
                $this->errorData = "No result!";
            }
            return [];
        } // if Is Error
        $ret = $cResult->GetArray();
        return $ret ? $ret : [];
    }

    public function getVersion(): mixed
    {
        return $GLOBALS['ADODB_vers'] ?? null;
    }

    /**
     * @param ?array $params Parameter set passed into the operation.
     */
    protected function _handleSql(?string $method = null, ?string $sql = null, ?array $params = null, bool $retArr = true): mixed
    {
        $cResult = $method ? $this->conn->$method($sql, $params) : true;

        $this->errorData = $this->conn->ErrorMsg();

        if ($this->errorData) {
            return $sql && $retArr ? [] : null;
        } // if Is Error

        return $sql && $retArr && !$cResult ? [] : $cResult;
    }

    protected function _logTime(int|float $t, string $sql): void
    {
        $dt = microtime(true) - (float)$t;
        if ($dt > 0.5) {
            error_log(date("d/m H-i-s") . ":\t" . $dt . "\t" . $sql . "\t" . $_SERVER['REQUEST_URI'] . "\n\n", 3, __DIR__ . "/../../../_logs/sql.log");
        }
    }
}

function adodb_error_handler(string $dbType, string $operation, int|float $errorNum, string $errMsg, mixed $mainParam, mixed $addParam, object $obj): void
{
    \service_container()->get('error')->database_error($dbType, $operation, $errorNum, $errMsg, $mainParam, $addParam, $obj);
}
