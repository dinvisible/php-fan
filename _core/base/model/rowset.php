<?php
declare(strict_types=1);

namespace fan\core\base\model;
use fan\project\exception\model\entity\fatal as fatalException;
/**
 * Description of rowset
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
class rowset extends \fan\core\base\data
{
    /**
     * Class of DB-table
     * @var fan\core\entity\table
     */
    protected ?object $entity = null;

    public function __construct(\fan\core\base\model\entity $entity, array &$data)
    {
        $this->entity = $entity;

        $rowClass = $entity->getRowClassName();
        foreach ($data as $k => &$v) {
            $this->set($k, new $rowClass($entity, $v, $this));
        }

        $this->_setSetter($this);
        $this->multiLevel = false;
    }

    // ======== Main Interface methods ======== \\

    public function toArray(bool $recursive = false): array
    {
        if (!$recursive) {
            return $this->data;
        }
        $ret = [];
        foreach ($this->data as $k => $v) {
            $ret[$k] = $v->toArray();
        }
        return $ret;
    }

    /**
     * @throws fatalException
     */
    public function getRowsById(): array
    {
        if (!$this->_isScalarId()) {
            throw new fatalException($this->getEntity(), 'Method "getRowsById" allowed only for Scalar Id!');
        }
        $ret = [];
        foreach ($this->data as $v) {
            $ret[$v->getId()] = $v;
        }
        return $ret;
    }

    /**
     * @throws fatalException
     */
    public function getArrayAssoc(string|array $fields = [], bool $excludeId = true, string|int|float|null $keyPrefix = null): array
    {
        if (!$this->_isScalarId()) {
            throw new fatalException($this->getEntity(), 'Method "getArrayAssoc" allowed only for Scalar Id!');
        }
        if (is_string($fields)) {
            if ($fields === '*') {
                $fields = [];
            } else {
                $fields = explode(',', $fields);
                $fields = array_map('trim', $fields);
            }
        }

        $ret = [];
        foreach ($this->data as $v) {
            $tmp = $v->toArray();
            if (!empty($fields)) {
                foreach ($tmp as $k1 => $v1) {
                    if (!in_array($k1, $fields)) {
                        unset($tmp[$k1]);
                    }
                }
                foreach ($fields as $k2) {
                    if (!isset($tmp[$k2])) {
                        $tmp[$k2] = null;
                    }
                }
            }
            if ($excludeId) {
                unset($tmp[$v->getId()]);
            }
            $k = $v->getId();
            if (!is_null($keyPrefix)) {
                $k = $keyPrefix . $k;
            }
            $ret[$k] = $tmp;
        }
        return $ret;
    }

    public function getColumn(string|int|float $columnName, bool $idAsKey = true): array
    {
        $idAsKey = $idAsKey && $this->_isScalarId();
        $ret = [];
        foreach ($this->data as $k => $v) {
            $ret[$idAsKey ? $v->getId() : $k] = $v[$columnName];
        }
        return $ret;
    }

    public function getArrayHash(mixed $keyField, mixed $valField): array
    {
        $ret = [];
        foreach ($this->data as $v) {
            $ret[$v[$keyField]] = $v[$valField];
        }
        return $ret;
    }

    public function getEntity(): \fan\core\base\model\entity
    {
        return $this->entity;
    }

    // ======== Private/Protected methods ======== \\

    protected function _isScalarId(): bool
    {
        return !is_array($this->getEntity()->description->getPrimeryKey());
    }

    // ======== Required Interface methods ======== \\

    public function serialize(): string {
        return \fan\core\adapter\safe_serializer::encodePhpSnapshot($this->__serialize());
    }

    public function __serialize(): array
    {
        if (empty($this->data)) {
            throw new \LogicException('An empty rowset can\'t be serialized!');
        }
        return parent::__serialize();
    }

    public function unserialize(string $recover): void {
        $recover = \fan\core\adapter\safe_serializer::decodePhpSnapshot((string)$recover);
        if (!is_array($recover)) {
            throw new \LogicException('A rowset snapshot must decode to an array.');
        }

        $this->__unserialize($recover);
    }

    public function __unserialize(array $recover): void
    {
        parent::__unserialize($recover);
        if (empty($this->data)) {
            throw new \LogicException('An empty rowset can\'t be unserialized!');
        }
        $this->entity = reset($this->data)->getEntity();
    }

}
