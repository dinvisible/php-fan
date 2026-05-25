<?php

declare(strict_types=1);

namespace fan\core\base\meta;
use fan\core\base\data;
use fan\core\base\meta\row as meta_row;

/**
 * Meta Data Row
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
class row extends data
{
    /**
     * @var \fan\core\block\all Linked block
     */
    protected ?object $block = null;

    /**
     * @var \fan\core\base\meta\maker
     */
    protected ?object $maker = null;

    /**
     * @var \fan\core\base\meta\row
     */
    protected ?object $parent = null;


    protected int|string|null $keyName = null;

    private $rowFactory = null;

    public function __construct(
        maker $maker,
        array $data,
        ?row $parent = null,
        int|string|null $keyName = null,
        ?callable $rowFactory = null
    )
    {
        $this->maker   = $maker;
        $this->block   = $maker->getBlock();
        $this->parent  = $parent;
        $this->keyName = $keyName;
        $this->rowFactory = $rowFactory ?? $maker->getRowFactory();
        $this->data    = $this->makeData($data);

        $this->_setSetter($maker);
        $this->_setSetter($this->block);
    }

    // ======== Main Interface methods ======== \\

    public function makeData(array $data): array
    {
        $ret = [];
        foreach ($data as $k => $v) {
            $ret[$k] = is_array($v) ? $this->createRow($v, $this, $k) : $v;
        }
        return $ret;
    }

    public function mergeData(array $data, bool $rewriteExisting = true): static
    {
        foreach ($data as $k => $v) {
            $this->set($k, $v, $rewriteExisting);
        }
        return $this;
    }

    // ======== Private/Protected methods ======== \\

    protected function _makeSubData(mixed $key, mixed $value): meta_row
    {
        return $this->createRow($value, $this, $key);
    }

    private function createRow(array $data, ?row $parent = null, int|string|null $keyName = null): row
    {
        if (!is_callable($this->rowFactory)) {
            throw new \RuntimeException('Meta row factory is not configured for meta row.');
        }

        $row = ($this->rowFactory)($this->maker, $data, $parent, $keyName, $this->rowFactory);
        if (!$row instanceof row) {
            $actual = is_object($row) ? get_class($row) : gettype($row);
            throw new \UnexpectedValueException('Meta row factory returned "' . $actual . '".');
        }

        return $row;
    }

}
