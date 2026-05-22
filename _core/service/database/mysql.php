<?php

declare(strict_types=1);

namespace fan\core\service\database;
/**
 *
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
 * @author: Otchenashenko Sergey (dinvisible@gmail.com)
 * @author: Alexandr Nosov (alex@4n.com.ua)
 * @version of file: 05.02.001 (10.03.2014)
 */
class mysql extends base
{

    /**
     * @var \mysqli Connection by mysqli
     */
    protected mixed $lConnent = null;

    protected string $parsedSql = '';

    // ======== Main Interface methods ======== \\
    public function reconnect(array $param, bool $makeException = true): bool
    {
        $this->parsedSql = '';

        if (!class_exists('\mysqli', false)) {
            $this->_fixError(
                    1,
                    'Connect to mysql server.',
                    0,
                    'MySQLi extension is not loaded.',
                    $makeException
            );
            return false;
        }

        $host = (string)(isset($param['HOST']) ? $param['HOST'] : ini_get('mysqli.default_host'));
        if (!empty($param['PERSISTENT']) && $host && substr($host, 0, 2) !== 'p:') {
            $host = 'p:' . $host;
        }
        $user     = (string)(isset($param['USER']) ? $param['USER'] : ini_get('mysqli.default_user'));
        $password = (string)(isset($param['PASSWORD']) ? $param['PASSWORD'] : ini_get('mysqli.default_pw'));
        try {
            $lConnent = mysqli_init();
            $isConnected = $lConnent instanceof \mysqli && $lConnent->real_connect($host, $user, $password);
        } catch (\mysqli_sql_exception $exception) {
            $lConnent = $lConnent ?? null;
            $isConnected = false;
            $connectionError = $exception->getMessage();
            $connectionCode = $exception->getCode();
        }

        if (empty($lConnent) || !$isConnected) {
            $this->_fixError(
                    1,
                    'Connect to mysql server.',
                    empty($lConnent) ? 0 : ($connectionCode ?? $lConnent->connect_errno),
                    'Could not connect: ' . (empty($lConnent) ? 'mysqli_init failed' : ($connectionError ?? $lConnent->connect_error)),
                    $makeException
            );
            return false;
        }
        try {
            $isSelected = $lConnent->select_db((string)$param['DATABASE']);
        } catch (\mysqli_sql_exception $exception) {
            $isSelected = false;
            $selectionError = $exception->getMessage();
            $selectionCode = $exception->getCode();
        }
        if ($isSelected) {
            $this->lConnent = $lConnent;
        } else {
            $this->_fixError(
                    2,
                    'Select mysql DB.',
                    $selectionCode ?? $lConnent->errno,
                    'Can\'t use DB "' . $param['DATABASE'] . '": ' . ($selectionError ?? $lConnent->error),
                    $makeException
            );
            return false;
        }
        return true;
    }

    public function connectionClose(): static
    {
        if (!empty($this->lConnent)) {
            $this->lConnent->close();
            $this->lConnent = null;
        }
        return $this;
    }

    public function execute(string $sql, ?array $param = null, mixed $resultType = null): array|bool|null
    {
        $this->parsedSql = '';

        if (empty($this->lConnent)) {
            $this->_fixError(
                    3,
                    'Prepare to execute SQL.',
                    0,
                    'Connect to MySQL isn\'t set.',
                    true
            );
            return null;
        }

        try {
            $queryResult = $this->lConnent->query($this->_parseSql($sql, $param, true));
        } catch (\mysqli_sql_exception $exception) {
            $queryResult = false;
            $queryError = $exception->getMessage();
            $queryCode = $exception->getCode();
        }
        if ($queryResult === false) {
            $this->_fixError(
                    4,
                    'Execute SQL.',
                    $queryCode ?? $this->lConnent->errno,
                    'Invalid query: ' . ($queryError ?? $this->lConnent->error),
                    false
            );
            return null;
        }

        if (!is_bool($queryResult)) {
            if (is_null($resultType) || !$this->_isValidType($resultType)) {
                $resultType = $this->resultType;
            }
            $result = [];
            do {
                $line = $queryResult->fetch_array($resultType);
                if ($line) {
                    $result[] = $line;
                }
            } while (!empty($line));
            $queryResult->free();
            return $result;
        }
        return $queryResult;
    }

    public function startTransaction(): bool|null
    {
        return $this->execute('START TRANSACTION');
    }

    public function setSavePoint(string $savePoint): bool|null
    {
        return $this->execute('SAVEPOINT ?', [$savePoint]);
    }

    public function commit(): bool|null
    {
        return $this->execute('COMMIT');
    }

    public function rollback(?string $savePoint = null): bool|null
    {
        return empty($savePoint) ? $this->execute('ROLLBACK') : $this->execute('ROLLBACK TO SAVEPOINT ?', [$savePoint]);
    }

    public function getInsertId(): mixed
    {
        if (empty($this->lConnent)) {
            return null;
        }
        $result = $this->execute('SELECT LAST_INSERT_ID() AS id', null, MYSQL_ASSOC);
        return $result[0]['id'];
    }

    public function getOne(string $sql, string $fieldName, ?array $param = null): mixed
    {
        $result = $this->execute($sql, $param, MYSQL_ASSOC);
        return empty($result) ? null : $result[0][$fieldName];
    }

    public function getRow(string $sql, ?array $param = null, ?int $resultType = null): array
    {
        $result = $this->execute($sql, $param, $resultType);
        return empty($result) ? [] : $result[0];
    }

    public function getRowAssoc(string $sql, ?array $param = null): array
    {
        return $this->getRow($sql, $param, MYSQL_ASSOC);
    }

    public function getCol(string $sql, string|int $colName, ?array $param = null): array
    {
        $result = [];
        $tmp = $this->execute($sql, $param, is_string($colName) ? MYSQL_ASSOC : MYSQL_NUM);
        if (!empty($tmp)) {
            foreach ($tmp as $v) {
                $result[] = isset($v[$colName]) ? $v[$colName] : null;
            }
        }
        return $result;
    }

    public function getAssoc(string $sql, ?array $param = null): array
    {
        $result = [];
        $tmp = $this->execute($sql, $param, MYSQL_ASSOC);
        if (!empty($tmp)) {
            foreach ($tmp as $v) {
                $k = array_shift($v);
                $result[$k] = $v;
            }
            return $result;
        }
        return [];
    }

    public function getAll(string $sql, ?array $param = null, int|string|null $resultType = null): array
    {
        $result = $this->execute($sql, $param, $resultType);
        return empty($result) ? [] : $result;
    }

    public function getAllLimit(string $sql, ?array $param = null, int|float $qtt = -1, int|float $offset = -1, int|string|null $resultType = null): array
    {
        if ($qtt > -1) {
            $sql .= ' LIMIT ';
                $sql .= $offset > -1 ? (int)$offset . ', ' . (int)$qtt : (int)$qtt;
        }
        return $this->getAll($sql, $param, $resultType);
    }

    public function getVersion(): ?string
    {
        if (empty($this->lConnent)) {
            return null;
        }
        $result = $this->execute('SELECT VERSION() AS ver', null, MYSQL_ASSOC);
        return 'MySQL ' . $result[0]['ver'];
    }

    public function getTableStatus(string $tableName): mixed
    {
        $result = $this->execute('SHOW TABLE STATUS LIKE ?', [$tableName]);
        return $result[0];
    }

    /**
     * Transforms sql between supported representations.
     */
    public function parseSql(string $sql, array $param): string
    {
        return $this->_parseSql($sql, $param, false);
    }

    public function getParsedSql(): string
    {
        return $this->parsedSql;
    }

    // ======== Private/Protected methods ======== \\
    protected function _parseSql(string $sql, mixed $param, bool $saveResult): string
    {
        if (!empty($param)) {

            if (!is_array($param)) {
                $param = [$param];
            }

            $sqlArr = explode('?', $sql);
            $sql = '';
            foreach ($param as $v) {
                if (empty($sqlArr)) {
                    throw new \InvalidArgumentException('Quantity of parameters more than quantity of placeholders.');
                } else {
                    $sql .= array_shift($sqlArr);
                }
                if (is_null($v)) {
                    $sql .= 'NULL';
                } else {
                    switch (gettype($v)) {
                    case 'integer' :
                        $sql .= $v;
                        break;
                    case 'double' :
                        $sql .= str_replace(',', '.', (string)$v);
                        break;
                    case 'boolean' :
                        $sql .= $v ? 1 : 0;
                        break;
                    case 'object' :
                        $v = method_exists($v, '__toString') ? $v->__toString() : (string)$v;
                    default:
                        if (is_scalar($v)) {
                            $v = (string)$v;
                            $sql .= '\'' . (empty($this->lConnent) ? addslashes($v) : $this->lConnent->real_escape_string($v)) . '\'';
                        } else {
                            throw new \InvalidArgumentException('Incorrect type of placeholder "' . gettype($v) . '".');
                        }
                    }
                }
            }
            if (!empty($sqlArr)) {
                if (count($sqlArr) > 1) {
                    throw new \InvalidArgumentException('Quantity of parameters less than quantity of placeholders.');
                }
                $sql .= implode('?', $sqlArr);
            }
        }
        if ($saveResult) {
            $this->parsedSql = $sql;
        }
        return $sql;
    }

}
