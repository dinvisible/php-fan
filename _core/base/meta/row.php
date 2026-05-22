<?php

declare(strict_types=1);

namespace fan\core\base\meta;
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
class row extends \fan\core\base\data
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


    public function __construct(maker $maker, array $data, ?row $parent = null, int|string|null $keyName = null)
    {
        $this->maker   = $maker;
        $this->block   = $maker->getBlock();
        $this->data    = $this->makeData($data);
        $this->parent  = $parent;
        $this->keyName = $keyName;

        $this->_setSetter($maker);
        $this->_setSetter($this->block);
    }

    // ======== Main Interface methods ======== \\

    public function makeData(array $data): array
    {
        $ret = [];
        foreach ($data as $k => $v) {
            $ret[$k] = is_array($v) ? new \fan\project\base\meta\row($this->maker, $v, $this, $k) : $v;
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

    protected function _makeSubData(mixed $key, mixed $value): \fan\core\base\meta\row
    {
        $class = get_class($this);
        return new $class($this->maker, $value, $this, $key);
    }

}
