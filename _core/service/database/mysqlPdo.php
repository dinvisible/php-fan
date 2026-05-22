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
class mysqlPdo extends base
{
    // ======== Main Interface methods ======== \\
    public function reconnect(array $param, bool $makeException = true): mixed
    {
    }

    public function connectionClose(): static
    {
        return $this;
    }

    public function execute(string $sql, ?array $param = null, ?int $resultType = null): mixed
    {
        return $result;
    }

    public function startTransaction(): mixed
    {
        return $this->execute('START TRANSACTION');
    }

    public function setSavePoint(string $savePoint): mixed
    {
        return $this->execute('SAVEPOINT ?', [$savePoint]);
    }

    public function commit(): mixed
    {
        return $this->execute('COMMIT');
    }

    public function rollback(?string $savePoint = null): mixed
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
        $tmp = $this->execute($sql, $param, MYSQL_ASSOC);
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
    public function parseSql(string $sql, array $param): mixed
    {
        return $this->_parseSql($sql, $param, false);
    }

    public function getParsedSql(): mixed
    {
        return $this->parsedSql;
    }

    // ======== Private/Protected methods ======== \\

}
