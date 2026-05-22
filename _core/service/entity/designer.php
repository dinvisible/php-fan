<?php

declare(strict_types=1);

namespace fan\core\service\entity;
use fan\project\exception\model\entity\fatal as fatalException;
/**
 * Designer of SQL-request
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
abstract class designer
{
    /**
     * SQL-request parts
     * @var string
     */
    protected array $queryParts = [];
    /**
     * Entity - table data
     * @var \fan\core\base\model\entity
     */
    protected ?object $entity = null;

    /**
     * Source Parameters
     * @var array
     */
    protected ?array $srcParam = null;
    /**
     * Result Parameters
     * @var array
     */
    protected ?array $adjustedParam = null;

    public function __construct(?\fan\core\base\model\entity $entity = null)
    {
        $this->entity = $entity;
    }

    // ======== Static methods ======== \\

    // ======== The magic methods ======== \\
    public function __set(string $partName, mixed $value): void
    {
        $this->set((string)$partName, $value);
    }

    public function __get(string $partName): mixed
    {
        return $this->get((string)$partName);
    }

    public function __toString(): string
    {
        return $this->assemble();
    }

    // ======== Main Interface methods ======== \\
    public function set(string $partName, mixed $partValue, bool $allowException = true): static
    {
        $normalizedPartValue = is_scalar($partValue) || $partValue === null ? (string)$partValue : $partValue;
        if ($normalizedPartValue !== '' && $this->_checkPartName($partName, $allowException)) {
            $this->queryParts[$partName] = $partValue;
        }
        return $this;
    }

    public function get(string $partName, bool $allowException = false): mixed
    {
        return $this->_checkPartName($partName, $allowException) ? $this->queryParts[$partName] : null;
    }

    public function add(string $partName, mixed $newPart, bool $toEnd = true, bool $allowException = true): static
    {
        $normalizedNewPart = is_scalar($newPart) || $newPart === null ? (string)$newPart : $newPart;
        if ($normalizedNewPart !== '') {
            $this->_checkPartName($partName, $allowException);
            $curParts = &$this->queryParts[$partName];
            if (!is_array($curParts)) {
                $curParts = empty($curParts) ? [] : [$curParts];
            }

            if (is_array($newPart)) {
                $curParts = $toEnd ? array_merge($curParts, $newPart) : array_merge($newPart, $curParts);
            } elseif (is_string($newPart)) {
                if ($toEnd) {
                    array_push($curParts, $newPart);
                } else {
                    array_unshift($curParts, $newPart);
                }
            }
        }
        return $this;
    }

    public function makeWhere(mixed $param, bool $merge = true): string|array
    {
        $result = [];

        $isIterator = false;
        if (is_object($param)) {
            if ($param instanceof \ArrayAccess && $param instanceof \Iterator) {
                $isIterator = true;
            } elseif (method_exists($param, 'toArray')) {
                $param = $param->toArray();
            } elseif (method_exists($param, '__toString')) {
                $param = $param->__toString();
            } else {
                $param = null;
            }
        }

        if (is_array($param) || $isIterator) {
            $adjustedParam = [];
            $i = 0;
            foreach ($param as $k => $v) {
                $result[$i] = $i === 0 ? 'WHERE ' : 'AND ';
                if (is_numeric($k)) {
                    $result[$i] .= $v;
                } elseif (is_array($v)) {
                    if (empty($v)) {
                        throw new \InvalidArgumentException('Empty array for field "' . $k . '" doesn\'t allowed for make "Where".');
                    } else {
                        $result[$i] .= strstr($k, '?') ? $k : '`' . $k . '` IN (' . implode(',', array_fill(0, count($v), ' ?')) . ')';
                        $adjustedParam = array_merge($adjustedParam, array_values($v));
                    }
                } elseif (is_null($v)) {
                    $result[$i] .= '`' . $k . '` IS null';
                } else {
                    $result[$i] .= strstr($k, '?') ? $k : '`' . $k . '` = ?';
                    $adjustedParam[] = $v;
                }
                $i++;
            }
            $this->adjustedParam = array_merge((array)$this->adjustedParam, $adjustedParam);
        } elseif (!is_null($param)) {
            $param = (string)$param;
            $result[0] = preg_match('/(?:^|\s)where\s/i', $param) ? $param : 'WHERE ' . $param;
            $param = null;
        }
        return $merge ? $this->_mergeParts($result) : $result;
    }
    public function assemble(mixed $param = null): string
    {
        if (!is_null($param)) {
            $this->srcParam = $param;
        }

        $query = $this->_mergeParts($this->queryParts);

        if (is_null($this->adjustedParam) && is_array($param)) {
            $this->adjustedParam = array_values($param);
        }

        return $query;
    }

    public function getAdjustedParam(): ?array
    {
        return $this->adjustedParam;
    }

    public function toArray(): array
    {
        return $this->queryParts;
    }

    public function getEntity(): ?\fan\core\base\model\entity
    {
        return $this->entity;
    }

    // ======== Private/Protected methods ======== \\
    /**
     * @throws fatalException
     */
    protected function _checkPartName(string $partName, bool $allowException): bool
    {
        if (!array_key_exists($partName, $this->queryParts)) {
            if ($allowException) {
                throw new fatalException($this->getEntity(), 'Incorrect key of SQL-part: "' . $partName . '".');
            }
            return false;
        }
        return true;
    }

    protected function _mergeParts(array $source): string
    {
        $result        = '';
        $adjustedParam = [];
        $isSnippet     = false;
        foreach ($source as $v) {
            if (!is_null($v)) {
                if (is_array($v)) {
                    $part = $this->_mergeParts($v);
                } elseif (is_object($v)) {
                    if ($v instanceof \fan\core\service\entity\snippet) {
                        list($part, $tmpParam) = $v->getSnippetQuery($this->srcParam);
                        if (!empty($tmpParam)) {
                            $adjustedParam = array_merge($adjustedParam, $tmpParam);
                        }
                        $isSnippet = true;
                    } elseif (method_exists($v, '__toString')) {
                        $part = $v->__toString();
                    } else {
                        throw new \InvalidArgumentException('Unknown object of SQL-part. Instance of "' . get_class_alt($v) . '".');
                    }
                } elseif (is_scalar($v)) {
                    $part = (string)$v;
                } else {
                    throw new \InvalidArgumentException('Incorrect type (' . gettype($v) . ') of SQL-part');
                }

                if ($result !== '' && $part !== '' && preg_match('/\S$/', $result) && preg_match('/^\S/', $part)) {
                    $result .= ' ';
                }
                $result .= $part;
            }
        }
        if ($isSnippet) {
            $this->adjustedParam = array_merge((array)$this->adjustedParam, $adjustedParam);
        }
        return $result;
    }

    protected function _makeSetupPart(array $data): string
    {
        $result = '';
        $adjustedParam = [];
        foreach ($data as $k => $v) {
            if (!empty($result)) {
                $result .= ', ';
            }
            if (is_null($v)) {
                $result  .= '`' . $k . '` = null';
            } else {
                $result  .= '`' . $k . '` = ?';
                $adjustedParam[] = $v;
            }
        }
        $this->adjustedParam = array_merge((array)$this->adjustedParam, $adjustedParam);
        return $result;
    }

}
