<?php

declare(strict_types=1);

namespace fan\core\service\entity\descriptor;
/**
 * Description of descriptor
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
abstract class mysql extends \fan\core\service\entity\descriptor
{
    /**
     * Info about fields
     * @var string
     */
    protected array $srcFields = [];
    /**
     * Info about keys
     * @var string
     */
    protected array $srcKeys = [];

    // ======== Static methods ======== \\
    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
    // ======== Main Interface methods ======== \\
    public function getPrimeryKey(): string|array|null
    {
        foreach ($this->getKeys() as $k0 => $v0) {
            if (strtoupper((string)$k0) === 'PRIMARY') {
                $primary = array_keys($v0['fields']);
                return count($primary) === 1 ? $primary[0] : $primary;
            }
        }
        return null;
    }

    public function getKeys(): array
    {
        $result = [];
        foreach ($this->_getKeys() as $v) {
            if (!isset($result[$v['Key_name']])) {
                $result[$v['Key_name']] = [
                    'type'    => $v['Index_type'],
                    'unique'  => !$v['Non_unique'],
                    'packed'  => $v['Packed'],
                    'comment' => $v['Index_comment'],
                    'fields'  => [],
                ];
            }
            $result[$v['Key_name']]['fields'][$v['Column_name']] = [
                'order'       => $v['Seq_in_index'],
                'subPart'     => isset($v['Sub_part']) ? $v['Sub_part'] : null,
                'collation'   => $v['Collation'],
                'null'        => strtoupper((string)$v['Null']) === 'YES',
                'cardinality' => isset($v['Cardinality']) ? $v['Cardinality'] : null,
            ];
        }
        return $result;
    }

    // ======== Private/Protected methods ======== \\
    protected function _resetDefaultVal(array &$field): static
    {
        if (is_null($field['default']) && !$field['null']) {
            if (in_array($field['type'], ['tinyint', 'smallint', 'mediumint', 'int', 'bigint', 'decimal', 'float', 'double', 'real', 'bit', 'boolean', 'serial', 'timestamp', 'year'])) {
                $field['default'] = 0;
            } elseif (in_array($field['type'], ['char', 'varchar', 'tinytext', 'text', 'mediumtext', 'longtext', 'binary', 'varbinary', 'tinyblob', 'mediumblob', 'blob', 'longblob'])) {
                $field['default'] = 0;
            }
        }
        return $this;
    }

    protected function _getKeys(): array
    {
        if (empty($this->srcKeys)) {
            $this->srcKeys = $this->connection->execute('SHOW KEYS FROM `' . $this->tableName . '`');
        }
        return $this->srcKeys;
    }

}
