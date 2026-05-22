<?php

declare(strict_types=1);

namespace fan\core\service\entity\descriptor\mysql;

/**
 * Get table description by information_schema
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
class schema extends \fan\core\service\entity\descriptor\mysql
{
    /**
     * Data Base Name
     * @var string
     */
    protected ?string $dbName = null;

    /**
     * Connection to database
     * @var \fan\core\service\database
     */
    protected ?object $connectionSchema = null;

    /**
     * Array of Table Info
     * @var string
     */
    protected ?array $tableInfo = null;

    /**
     * Array of Constraints
     * @var string
     */
    protected ?array $constraints = null;

    public function __construct(\fan\core\service\entity\description $description)
    {
        parent::__construct($description);
//throw new \fan\project\exception\model\reverse($description->getEntity());

        $checkParam = [
            'ENGINE'     => null,
            'PERSISTENT' => 0,
            'HOST'       => 'localhost',
            'DATABASE'   => null,
            'USER'       => null,
            'PASSWORD'   => '',
        ];
        $connect = $description->getEntity()->getConnection();
        $param = $connect->getConnectionParam();
        foreach ($checkParam as $k => $v) {
            if (!isset($param[$k])) {
                if (is_null($v)) {
                    throw new \project\exception\model\reverse($description->getEntity(), 'Undefined required connect parameter ' . $k . ' for connection ' . $connect->getConnectionName());
                } else {
                    $param[$k] = $v;
                }
            }
        }

        try {
            $this->connectionSchema = $this->containerService('database_by_param', [
                'ENGINE'     => $param['ENGINE'],
                'PERSISTENT' => $param['PERSISTENT'],
                'HOST'       => $param['HOST'],
                'DATABASE'   => 'information_schema',
                'USER'       => $param['USER'],
                'PASSWORD'   => $param['PASSWORD'],
                'SCENARIO'   => '',
            ], 'shema');
        } catch (\fan\project\exception\database $exp) {
            $exp->disableLog();
            throw new \fan\project\exception\model\reverse($description->getEntity());
        }

        $this->dbName = (string)$param['DATABASE'];
    }

    // ======== Static methods ======== \\
    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
    // ======== Main Interface methods ======== \\
    public function isTableExists(): bool
    {
        $info = $this->_getTableInfo();
        return !empty($info);
    }
    public function getFields(): array
    {
        $result = [];
        foreach ($this->_getFields() as $v) {
            //Doesn't used: CHARACTER_MAXIMUM_LENGTH, CHARACTER_OCTET_LENGTH, NUMERIC_PRECISION, NUMERIC_SCALE, COLUMN_KEY
            preg_match('/^\w+\((.*)\)\s*(.*)$/', (string)$v['COLUMN_TYPE'], $matches);
            $field = [
                'type'           => $v['DATA_TYPE'],
                'length'         => empty($matches) ? null : $matches[1],
                'default'        => empty($v['COLUMN_DEFAULT']) ? null : $v['COLUMN_DEFAULT'],
                'collation'      => $v['COLLATION_NAME'],
                'charset'        => $v['CHARACTER_SET_NAME'],
                'attribute'      => empty($matches) ? null : $matches[2],
                'null'           => strtoupper((string)$v['IS_NULLABLE']) === 'YES',
                'auto_increment' => strtolower((string)$v['EXTRA']) === 'auto_increment',
                'comment'        => empty($v['COLUMN_COMMENT']) ? null : $v['COLUMN_COMMENT'],
                //'mime_type'      => '',
            ];

            $this->_resetDefaultVal($field);

            $result[(string)$v['COLUMN_NAME']] = $field;
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
        foreach ($this->_getConstraints() as $v) {
            $result[] = [
                'name'      => $v['CONSTRAINT_NAME'],
                'field'     => $v['COLUMN_NAME'],
                'ref_db'    => (string)$v['UNIQUE_CONSTRAINT_SCHEMA'] === (string)$this->dbName ? null : $v['UNIQUE_CONSTRAINT_SCHEMA'],
                'ref_table' => $v['REFERENCED_TABLE_NAME'],
                'ref_field' => $v['REFERENCED_COLUMN_NAME'],
                'on_delete' => strtolower($v['DELETE_RULE']),
                'on_update' => strtolower($v['UPDATE_RULE']),
            ];
        }

        return $result;
    }

    public function getEngine(): mixed
    {
        $info = $this->_getTableInfo();
        return $info['ENGINE'];
    }
    public function getCreateTime(): mixed
    {
        $info = $this->_getTableInfo();
        return $info['CREATE_TIME'];
    }
    public function getTableCollation(): mixed
    {
        $info = $this->_getTableInfo();
        return $info['TABLE_COLLATION'];
    }
    public function getComment(): mixed
    {
        $info = $this->_getTableInfo();
        return $info['TABLE_COMMENT'];
    }

    // ======== Private/Protected methods ======== \\
    protected function _getTableInfo(): array
    {
        if (is_null($this->tableInfo)) {
            $tmp = $this->connectionSchema->getAll(
                    '
SELECT
    *
FROM `TABLES`
WHERE
    `TABLE_SCHEMA` = ?
    AND `TABLE_NAME` = ?',
                    [$this->dbName, $this->tableName]
            );
            $this->tableInfo = empty($tmp[0]) ? [] : $tmp[0];
        }
        return $this->tableInfo;
    }

    protected function _getFields(): array
    {
        if (empty($this->srcFields)) {
            $this->srcFields = $this->connectionSchema->getAll(
                    '
SELECT
    *
FROM `COLUMNS`
WHERE
    `TABLE_SCHEMA` = ?
    AND `TABLE_NAME` = ?
ORDER BY
    `ORDINAL_POSITION`',
                    [$this->dbName, $this->tableName]
            );
        }
        return $this->srcFields;
    }

    protected function _getConstraints(): array
    {
        if (is_null($this->constraints)) {
            $tmp = $this->connectionSchema->execute(
                    '
SELECT
    REF.*,
    USG.`COLUMN_NAME`,
    USG.`REFERENCED_COLUMN_NAME`
FROM
    `REFERENTIAL_CONSTRAINTS` AS REF
INNER JOIN `KEY_COLUMN_USAGE` AS
    USG ON USG.`CONSTRAINT_NAME` = REF.`CONSTRAINT_NAME`
WHERE
    REF.`CONSTRAINT_SCHEMA` = ?
    AND REF.`TABLE_NAME` = ?
    AND USG.`CONSTRAINT_SCHEMA` = ?
    AND USG.`TABLE_NAME` = ?
ORDER BY
    USG.`ORDINAL_POSITION`',
                    [$this->dbName, $this->tableName, $this->dbName, $this->tableName]
            );
            $this->constraints = empty($tmp) ? [] : $tmp;
        }
        return $this->constraints;
    }
}
