<?php
declare(strict_types=1);

namespace fan\core\base\model;
use fan\core\base\data;
use fan\core\base\model\entity;
use fan\core\base\model\row;

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
class rowset extends data
{
    /**
     * Class of DB-table
     * @var fan\core\entity\table
     */
    protected ?object $entity = null;

    public function __construct(
        entity $entity,
        array &$data,
        callable $rowFactory,
        ?callable $snapshotEncoder = null,
        ?callable $snapshotDecoder = null
    )
    {
        parent::__construct(null, null, null, null, null, $snapshotEncoder, $snapshotDecoder);
        $this->entity = $entity;

        $rowClass = $entity->getRowClassName();
        foreach ($data as $k => &$v) {
            $row = $rowFactory($rowClass, $entity, $v, $this);
            if (!$row instanceof row) {
                $actual = is_object($row) ? get_class($row) : gettype($row);
                throw new \UnexpectedValueException('Row factory returned "' . $actual . '".');
            }
            $this->set($k, $row);
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

    public function getRowsById(): array
    {
        if (!$this->_isScalarId()) {
            throw $this->createRowsetFatalException('Method "getRowsById" allowed only for Scalar Id!');
        }
        $ret = [];
        foreach ($this->data as $v) {
            $ret[$v->getId()] = $v;
        }
        return $ret;
    }

    public function getArrayAssoc(string|array $fields = [], bool $excludeId = true, string|int|float|null $keyPrefix = null): array
    {
        if (!$this->_isScalarId()) {
            throw $this->createRowsetFatalException('Method "getArrayAssoc" allowed only for Scalar Id!');
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

    public function getEntity(): entity
    {
        return $this->entity;
    }

    // ======== Private/Protected methods ======== \\

    protected function _isScalarId(): bool
    {
        return !is_array($this->getEntity()->description->getPrimeryKey());
    }

    private function createRowsetFatalException(string $message, int $code = E_USER_ERROR, ?\Throwable $previous = null): \Throwable
    {
        $exception = $this->getEntity()->createRowsetFatalException($message, $code, $previous);
        if (!$exception instanceof \Throwable) {
            $actual = is_object($exception) ? get_class($exception) : gettype($exception);
            throw new \UnexpectedValueException('Model rowset exception factory returned "' . $actual . '".');
        }

        return $exception;
    }

    // ======== Required Interface methods ======== \\

    public function serialize(): string {
        return ($this->snapshotEncoder())($this->__serialize());
    }

    public function __serialize(): array
    {
        if (empty($this->data)) {
            throw new \LogicException('An empty rowset can\'t be serialized!');
        }
        return parent::__serialize();
    }

    public function unserialize(string $recover): void {
        $recover = ($this->snapshotDecoder())((string)$recover);
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
