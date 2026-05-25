<?php
declare(strict_types=1);

namespace fan\core\view;
use fan\core\base\data;
use fan\core\block\base;
use fan\core\view\router;

/**
 * View data-keeper
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
class keeper extends data
{
    /**
     * @var \fan\core\view\router
     */
    protected ?object $router = null;

    public function __construct(router $router)
    {
        $this->router = $router;
        $this->_setSetter($router);
        $this->_setSetter($router->getBlock());
        $this->multiLevel = false;
    }

    // ======== Main Interface methods ======== \\

    public function get(mixed $key = null, mixed $default = null, bool $logError = true): mixed
    {
        return is_null($key) ? $this->toArray() : parent::get($key, $default, $logError);
    }

    public function set(mixed $key, mixed $value, bool $rewriteExisting = true, ?bool $convArray = null): static
    {
        if (is_null($key) && $this->isFullRewrite()) {
            $this->data = $value;
        } else {
            parent::set($key, $value, $rewriteExisting, $convArray);
        }
        return $this;
    }

    public function clear(): static
    {
        if ($this->_checkSetter()) {
            $this->data = [];
        }
        return $this;
    }

    public function getRouter(): router
    {
        return $this->router;
    }

    public function getBlock(): base
    {
        return $this->router->getBlock();
    }

}
