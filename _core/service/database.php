<?php

declare(strict_types=1);

namespace fan\core\service;
use fan\project\exception\service\fatal as fatalException;
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
 * Database manager service
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
class database extends \fan\core\base\service\multi
{
    private static array $instances = [];

    /**
     * Index of Current instance
     * @var \fan\core\service\database
     */
    private static ?object $currentInstance = null;

    /**
     * Index of Default instance
     * @var \fan\core\service\database
     */
    private static ?object $defaultInstance = null;

    private static array $paramKeys = ['ENGINE', 'PERSISTENT', 'HOST', 'DATABASE', 'USER', 'PASSWORD', 'SCENARIO'];


    protected ?string $connectionName = null;

    /**
     * @var \fan\core\service\database\base Current Engine
     */
    protected ?object $engine = null;

    /**
     * @var \fan\core\exception\service\database Database exception
     */
    protected ?object $exception = null;

    /**
     * Levels marked by number:
     *   0 - AUTOCOMMIT (NO TRANSACTION);
     *   1 - READ UNCOMMITTED;
     *   2 - READ COMMITTED;
     *   3 - REPEATABLE READ;
     *   4 - SERIALIZABLE;
     * @var integer Isolation Level of Transaction
     */
    protected int $isolationLevel = 0;

    protected bool $autoTransaction = true;

    protected bool $isTransaction = false;

    protected bool $isError = false;

    protected ?string $errorMessage = null;

    protected function __construct(?string $connectionName = null, mixed $extraKey = 0, ?array $param = null)
    {
        $this->exceptionDbOper = 'nothing';
        parent::__construct();
        $config = $this->getConfig();

        if (empty($param)) {
            if (!$connectionName) {
                $connectionName = $config->get('DEFAULT_CONNECTION');
            } elseif (!$config->get(['DATABASE', $connectionName])) {
                $this->errorMessage = 'Undefind connection name: "' . $connectionName . '"';
            }
            if (empty($this->errorMessage)) {
                $tmp = $config->get(['DATABASE', $connectionName]);
                if ($tmp instanceof \fan\core\service\config\row) {
                    $param = $tmp->toArray();
                } else {
                    $this->errorMessage = 'Can\'t find Parameters for connection name: "' . $connectionName . '"';
                }
            }
        }

        if ($this->errorMessage) {
            $this->isError = true;
            throw new fatalException($this, $this->errorMessage);
        }

        $connectionName = (string)$connectionName;
        $extraKey = self::_getInstanceKey($extraKey);
        $this->connectionName = $connectionName;

        self::$instances[$connectionName][$extraKey] = $this;
        if ($connectionName === (string)$config->get('DEFAULT_CONNECTION')) {
            self::$defaultInstance = $this;
        }

        $engine = (string)(empty($param['ENGINE']) ? $config->get('DEFAULT_ENGINE') : $param['ENGINE']);
        $class  = $this->_getEngine($engine, false);
        $this->engine = new $class($this, $param);
        $this->engine->reconnect($param, true);

        if (!$this->isError()) {
            $scenario = isset($param['SCENARIO']) ? $param['SCENARIO'] : $config->get('DEFAULT_SCENARIO');
            if ($scenario) {
                $this->runScenario($scenario);
            }
        }
    }

    public function __destruct()
    {
        $this->commit();
    }

    // ======== Static methods ======== \\

    public static function instance(?string $connectionName = null, mixed $extraKey = 0): self
    {
        $extraKey = self::_getInstanceKey($extraKey);
        if ($connectionName) {
            if (!isset(self::$instances[$connectionName][$extraKey])) {
                new self($connectionName, $extraKey);
            }
            self::$currentInstance = self::$instances[$connectionName][$extraKey];
        } else {
            if (!self::$defaultInstance) {
                self::$defaultInstance = new self(null, $extraKey);
            }
            self::$currentInstance = self::$defaultInstance;
        }
        return self::$currentInstance;
    }

    public static function instanceByParam(mixed $param, mixed $extraKey = 0): self
    {
        if (is_object($param) && method_exists($param, 'toArray')) {
            $param = $param->toArray();
        }
        $param = is_array($param) ? $param : [];
        $extraKey = self::_getInstanceKey($extraKey);
        $connectionName = self::_getConnectionName($param);
        if (!isset(self::$instances[$connectionName][$extraKey])) {
            new self($connectionName, $extraKey, $param);
        }
        return self::$instances[$connectionName][$extraKey];
    }

    public static function close(): void
    {
        if (self::$instances) {
            foreach (self::$instances as $k => $instanses) {
                foreach ($instanses as $inst) {
                    $inst->commit();
                    $inst->connectionClose();
                }
                unset(self::$instances[$k]);
            }
        }
    }

    public static function getCurrentInstance(): \fan\core\service\database
    {
        return self::$currentInstance ? self::$currentInstance : self::staticContainerService('database');
    }

    public static function getAllInstances(): array
    {
        return self::$instances;
    }

    public static function commitAll(): void
    {
        self::fixAll('commit', false);
    }
    public static function rollbackAll(bool $setError = true): void
    {
        self::fixAll('rollback', $setError);
    }
    public static function fixAll(string $oper, bool $setError = true): void
    {
        if ($oper && in_array($oper, ['commit', 'rollback']) && self::$instances) {
            foreach (self::$instances as $instanses) {
                foreach ($instanses as $inst) {
                    if ($oper === 'commit') {
                        $inst->commit();
                    } else {
                        $inst->rollback(null, $setError);
                    }
                }
            }
        }
    }


    // ======== Main Interface methods ======== \\
    public function getConnectionName(): ?string
    {
        return $this->connectionName;
    }
    public function getConnectionParam(): array
    {
        return $this->config->get(['DATABASE', $this->connectionName])->toArray();
    }

    public function setResultTypes(int|string $resultType = MYSQL_ASSOC): static
    {
        $this->engine->setResultTypes($resultType);
        return $this;
    }

    public function getErrorMessage(): ?string
    {
        if (is_null($this->errorMessage)) {
            $errData = $this->engine->getErrorData();
            $this->errorMessage = array_val($errData, 'err_msg');
        }
        return $this->errorMessage;
    }

    public function isError(): bool
    {
        return $this->isError;
    }

    public function resetError(): static
    {
        $this->isError      = false;
        $this->errorMessage = null;
        $this->exception    = null;
        $this->engine->resetError();
        return $this;
    }

    public function startTransaction(): static
    {
        if ($this->isolationLevel > 0 && !$this->isError) {
            $this->engine->startTransaction();
            $this->isTransaction = true;
        }
        return $this;
    }

    public function setAutoTransaction(bool $autoTransaction): static
    {
        $this->autoTransaction = !empty($autoTransaction);
        return $this;
    }

    public function setSavePoint(string $savePoint): static
    {
        if ($this->isTransaction && !$this->isError) {
            $this->engine->setSavePoint($savePoint);
        }
        return $this;
    }

    public function commit(): static
    {
        if ($this->isTransaction && !$this->isError) {
            $this->engine->commit();
            $this->isTransaction = false;
        }
        return $this;
    }

    public function rollback(?string $savePoint = null, bool $setError = true): static
    {
        if ($this->isTransaction) {
            $this->engine->rollback($savePoint);
            $this->isTransaction = false;
        }

        if (!$this->isError && $setError) {
            $this->isError      = true;
            $this->errorMessage = 'ROLLBACK';
        }
        //\fan\project\service\entity::fullClearEntity();
        return $this;
    }

    public function runScenario(string $scenario, array $data = []): static
    {
        $conf = $this->getConfig(['SCENARIO', $scenario]);
        if ($conf && $conf['SQL']) {
            $autoTransaction = $this->autoTransaction;
            $this->autoTransaction = false;
            if (!is_null($conf['ISOLATION_LEVEL'])) {
                $level = (int)$conf['ISOLATION_LEVEL'];
                if ($level < 0 || $level > 4) {
                    throw new fatalException($this, 'Incorrect isolation level: "' . $conf['ISOLATION_LEVEL'] . '"');
                }
                $this->isolationLevel = $level;
            }
            foreach ($conf['SQL'] as $k => $v) {
                $this->execute($v, isset($data[$k]) ? $data[$k] : []);
            }
            $this->autoTransaction = $autoTransaction;
        }
        return $this;
    }

    public function execute(string $sql, ?array $param = null): mixed
    {
        $this->_checkSql($sql);
        if ($this->isError) {
            return null;
        }

        if (!$this->isTransaction && $this->autoTransaction && preg_match('/^\s*(:?INSERT|UPDATE|DELETE|REPLACE)/i', $sql)) {
            $this->startTransaction();
        }

        $sql    = $this->_languageCorrection($sql, false);
        $time   = $this->config['LOG_MORE_THAN'] || $this->config['MAIL_MORE_THAN'] ? microtime(true) : 0;
        $result = $this->engine->execute($sql, $param);
        $this->_fixExecuteTime($time, $sql, $param);

        $errorMessage = $this->getErrorMessage();
        if ($errorMessage) {
            $this->isError = true;
            $this->_setErrorMessage($errorMessage);
            $this->rollback();
        }

        return $result;
    }

    public function getInsertId(): mixed
    {
        return $this->engine->getInsertId();
    }

    public function getOne(string $sql, string $fieldName, ?array $param = null): mixed
    {
        return $this->_executeEngine('getOne', $sql, [$fieldName, $param]);
    }

    public function getRow(string $sql, ?array $param = null, ?int $resultType = null): mixed
    {
        return $this->_executeEngine('getRow', $sql, [$param, $resultType]);
    }

    public function getRowAssoc(string $sql, ?array $param = null): mixed
    {
        return $this->_executeEngine('getRowAssoc', $sql, [$param]);
    }

    public function getCol(string $sql, string $colName, ?array $param = null): mixed
    {
        return $this->_executeEngine('getCol', $sql, [$colName, $param]);
    }

    public function getAssoc(string $sql, ?array $param = null): mixed
    {
        return $this->_executeEngine('getAssoc', $sql, [$param]);
    }

    public function getAll(string $sql, ?array $param = null): mixed
    {
        return $this->_executeEngine('getAll', $sql, [$param]);
    }

    public function getVersion(): mixed
    {
        return $this->engine->getVersion();
    }

    public function getAllLimit(string $sql, ?array $param = null, int|float $qtt = -1, int|float $offset = -1): mixed
    {
        return $this->_executeEngine('getAllLimit', $sql, [$param, $qtt, $offset]);
    }

    public function getTableStatus(string $tableName): mixed
    {
        return $this->engine->getTableStatus($tableName);
    }

    public function connectionClose(): void
    {
        $this->engine->connectionClose();
    }

    public function reconnect(bool $makeException = true): mixed
    {
        return $this->engine->reconnect($this->config['DATABASES'][$this->connectionName]->toArray(), $makeException);
    }

    /**
     * Transforms sql between supported representations.
     */
    public function parseSql(string $sql, array $param): mixed
    {
        return $this->engine->parseSql($sql, $param);
    }

    public function getParsedSql(): mixed
    {
        return $this->engine->getParsedSql();
    }

    /**
     * @throws \fan\core\exception\service\database
     */
    public function fixError(\fan\core\service\database\base $engine, bool $makeException): static
    {
        $err = $this->containerService('error');
        /* @var $err \fan\core\service\error */
        $errData = $engine->getErrorData();
        if (!empty($errData) && empty($this->exception)) {
            $this->isError      = true;
            $this->errorMessage = $errData['err_msg'];
            if ($makeException) {
                $this->exception = new \fan\project\exception\service\database(
                        $this,
                        $errData['oper_code'],
                        $errData['oper_msg'],
                        $errData['err_code'],
                        $errData['err_msg'],
                        $errData['sql']
                );
                throw $this->exception;
            }
            $err->logDatabaseError($this->connectionName, $errData['oper_msg'], $errData['err_msg'], $errData['err_code'], $errData['sql']);
        } else {
            $err->logErrorMessage('Incorrect call method "fixError". Error data is empty.', 'Incorrect call Database service');
        }
        return $this;
    }

    // ======== Private/Protected methods ======== \\
    protected static function _getConnectionName(array &$param): string
    {
        $tmp = [];
        foreach (self::$paramKeys as $k) {
            $tmp[$k] = isset($param[$k]) ? $param[$k] : null;
        }
        $param = $tmp;

        $nameParam = [];
        foreach (['HOST', 'DATABASE', 'USER', 'SCENARIO'] as $k) {
            if (!empty($param[$k])) {
                $nameParam[] = (string)$param[$k];
            }
        }
        return 'ByParameters:' . implode('/', $nameParam);
    }

    /**
     * Normalizes dynamic instance identifiers to valid PHP array keys.
     */
    private static function _getInstanceKey(mixed $extraKey): int|string
    {
        if (is_int($extraKey) || is_string($extraKey)) {
            return $extraKey;
        }
        if (is_bool($extraKey) || is_float($extraKey) || is_null($extraKey)) {
            return (string)$extraKey;
        }
        return \fan\core\adapter\safe_serializer::stableKey($extraKey);
    }

    protected function _executeEngine(string $methodName, string $sql, array $arguments): mixed
    {
        $this->_checkSql($sql);
        $sql = $this->_languageCorrection($sql, true);
        array_unshift($arguments, $sql);
        $time   = $this->config['LOG_MORE_THAN'] || $this->config['MAIL_MORE_THAN'] ? microtime(true) : 0;
        $result = call_user_func_array([$this->engine, $methodName], $arguments);
        $this->_fixExecuteTime($time, (string)$this->getParsedSql());

        return $result;
    }

    protected function _languageCorrection(string $sql, bool $isCoalesce = true): string
    {
        $matches = [];
        if ($this->getConfig('SQL_LNG_CORRECTION', true) && preg_match_all('/\W(\{((?:\w+\.)?\`?\w+)(\`?)\})\W/', $sql, $matches, PREG_SET_ORDER)) {
            $sl = $this->containerService('locale');
            $lngCur = '_' . $sl->getLanguage();
            $lngDef = '_' . $sl->getDefaultLanguage();

            foreach ($matches as $v) {
                if (false && $lngCur !== $lngDef && $isCoalesce) { // "false && " - is temporary hack: don't use "COALESCE"
                    $newVal = 'COALESCE(' . $v[2] . $lngCur . $v[3] . ', ' . $v[2] . $lngDef . $v[3] . ')';
                } else {
                    $newVal = $v[2] . $lngCur . $v[3];
                }
                $sql = str_replace($v[1], $newVal, $sql);
            }
        }
        return $sql;
    }

    /**
     * @throws \fan\core\exception\service\database
     * @throws fatalException
     */
    protected function _checkSql(string $sql): static
    {
        if (!empty($this->exception)) {
            throw $this->exception;
        }
        if (empty($sql)) {
            throw new fatalException($this, 'SQL-request is empty.');
        }
        return $this;
    }

    protected function _setErrorMessage(string $errorMessage): void
    {
        $this->errorMessage = $errorMessage;
    }

    protected function _fixExecuteTime(int|float $time, string $sql): void
    {
        if ($time > 0) {
            $time   = microtime(true) - $time;

            $msg    = 'Time=<b>' . $time . 's;</b>';
            $sql    = '<pre style="color:#444444;">' . htmlentities($sql, ENT_NOQUOTES, 'UTF-8') . '</pre>';

            $config = $this->config;
            if ($config['LOG_MORE_THAN'] && $time * 1000 >= $config['LOG_MORE_THAN']) {
                \fan\project\service\log::instance()->logMessage('sql_execute', $msg, 'SQL overtime', $sql);
            }
            if ($config['MAIL_MORE_THAN'] && $time * 1000 >= $config['MAIL_MORE_THAN']) {
                $this->containerService('error')->makeErrorEmail('overtime', 'SQL overtime', $msg . '<br /><br />' . $sql);
            }
        }
    }

}
