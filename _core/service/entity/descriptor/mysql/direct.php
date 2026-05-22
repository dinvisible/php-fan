<?php

declare(strict_types=1);

namespace fan\core\service\entity\descriptor\mysql;

/**
 * Get table description by SQL-requests: "DESCRIBE table", "SHOW KEYS FROM table", "SHOW CREATE TABLE table",
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
class direct extends \fan\core\service\entity\descriptor\mysql
{
    /**
     * SQL-request for create table
     * @var string
     */
    protected string $createTable = '';

    // ======== Static methods ======== \\
    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
    // ======== Main Interface methods ======== \\
    public function isTableExists(): bool
    {
        $tmp = $this->connection->execute('SHOW  TABLES LIKE \'' . $this->tableName . '\'');
        return !empty($tmp);
    }
    public function getFields(): array
    {
        $result = [];
        foreach ($this->_getFields() as $v) {
            $matches = [];
            preg_match('/^(\w+)\s*(?:\((.*)\)\s*(.*))?$/', (string)$v['Type'], $matches);
            $field = [
                'type'           => strtolower($matches[1]),
                'length'         => isset($matches[2]) ? $matches[2] : null,
                'default'        => empty($v['Default']) ? null : $v['Default'],
                'collation'      => null,
                'charset'        => null,
                'attribute'      => isset($matches[3]) ? $matches[3] : null,
                'null'           => strtoupper((string)$v['Null']) === 'YES',
                'auto_increment' => strpos((string)$v['Extra'], 'auto_increment') !== false,
                'comment'        => null,
                //'mime_type'      => null,
            ];

            $this->_resetDefaultVal($field);

            // Field name
            $pattern = '^\s*\`' . preg_replace('/\W/u', '\\\\$0', (string)$v['Field']) . '\`';
            // Field type
            $pattern .= '\s+\w+(?:\(.*?\))?';
            // Character set / Collate
            $pattern .= '\s*(?:CHARACTER\s+SET\s+(\w+))?\s*(?:COLLATE\s+(\w+))?';
            // Comment
            $pattern .= '.*?(?:COMMENT\s+\'(.+)\')?\,?$';

            if (preg_match('/' . $pattern . '/imu', $this->_getCreateTable(), $matches)) {
                $field['comment'] = empty($matches[3]) ? null : $matches[3];
                if (!empty($matches[1]) || !empty($matches[2])) {
                    $field['collation']  = implode(' ', [strval($matches[1]), strval($matches[2])]);
                }
            }
            $result[(string)$v['Field']] = $field;
        }

        foreach ($this->getKeys() as $k0 => $v0) {
            foreach ($v0['fields'] as $k1 => $v1) {
                $result[$k1]['keys'][] = $k0;
            }
        }
        return $result;
    }

    public function getRelations(): array
    {
        $result = [];

        // Constraint name
        $pattern = '^\s*CONSTRAINT\s+\`([^\`]+)\`';
        // Foreign key
        $pattern .= '\s+FOREIGN\s+KEY\s+\(\`([^\`]+)\`\)';
        // References
        $pattern .= '\s+REFERENCES\s+(?:\`([^\`]+)\`\.)?\`([^\`]+)\`\s*\(\`([^\`]+)\`\)';
        // Delete
        $pattern .= '(?:\s+ON\s+DELETE\s+(CASCADE|SET\sNULL|NO\sACTION|RESTRICT))?';
        // Update
        $pattern .= '(?:\s+ON\s+UPDATE\s+(CASCADE|SET\sNULL|NO\sACTION|RESTRICT))?';
        $pattern .= '\,?\s*$';

        $createTable = $this->_getCreateTable();
        $matches = null;
        if (preg_match_all('/' . $pattern . '/imu', $createTable, $matches)) {
            foreach ($matches[0] as $k => $v) {
                $result[] = [
                    'name'      => $matches[1][$k],
                    'field'     => $matches[2][$k],
                    'ref_db'    => empty($matches[3][$k]) ? null : $matches[3][$k],
                    'ref_table' => $matches[4][$k],
                    'ref_field' => $matches[5][$k],
                    'on_delete' => empty($matches[6][$k]) ? 'restrict' : strtolower($matches[6][$k]),
                    'on_update' => empty($matches[7][$k]) ? 'restrict' : strtolower($matches[7][$k]),
                ];
            }
        }
        return $result;
    }


    public function getEngine(): mixed
    {
        $createTable = $this->_getCreateTable();
        return null; // ToDo: this
    }
    public function getCreateTime(): mixed
    {
        $createTable = $this->_getCreateTable();
        return null; // ToDo: this
    }
    public function getTableCollation(): mixed
    {
        $createTable = $this->_getCreateTable();
        return null; // ToDo: this
    }
    public function getComment(): string
    {
        $result = '';
        $matches = null;
        if (preg_match('/\sCOMMENT\=\'(.+?)\'/iu', $this->_getCreateTable(), $matches)) {
            $result = $matches[1];
        }
        return $result;
    }

    // ======== Private/Protected methods ======== \\
    protected function _getFields(): array
    {
        if (empty($this->srcFields)) {
            $this->srcFields = $this->connection->execute('DESCRIBE `' . $this->tableName . '`');
        }
        return $this->srcFields;
    }

    protected function _getCreateTable(): string
    {
        if (empty($this->createTable)) {
            $tmp = $this->connection->execute('SHOW CREATE TABLE `' . $this->tableName . '`');
            $this->createTable = (string)$tmp[0]['Create Table'];
        }
        return $this->createTable;
    }

}
