<?php
declare(strict_types=1);

namespace fan\core\view\keeper\loader;
use fan\core\view\keeper;
use fan\core\view\router;
use fan\core\view\router\loader;

/**
 * View-data keeper of Block data for loader JSON-data
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
class text extends keeper
{
    public function __construct(router $router)
    {
        parent::__construct($router);
        $this->fullRewrite = true;
    }

    // ======== Static methods ======== \\

    // ======== Main Interface methods ======== \\

    public function get(mixed $key = null, mixed $default = null, bool $logError = true): mixed
    {
        $result = $this->__toString();
        return empty($result) ? $default : $result;
    }

    public function set(mixed $key, mixed $value, bool $rewriteExisting = true, ?bool $convArray = null): static
    {
        if (!is_numeric($key)) {
            $key = empty($key) ? 0 : 1;
        }

        if (empty($key) && $this->isFullRewrite()) {
            $this->data = [$value];
        } elseif ($key < 0) {
            array_unshift($this->data, $value);
        } else {
            array_push($this->data, $value);
        }
        return $this;
    }

    public function addRouter(loader $router): static
    {
        $this->_setSetter($router);
        $this->_setSetter($router->getBlock());
        return $this;
    }

    // ======== Private/Protected methods ======== \\

    // ======== The magic methods ======== \\

    public function __set(string $key, mixed $value): void
    {
        $this->set($value, (int)$key);
    }

    public function __get(string $key): mixed
    {
        return $this->get(null);
    }

    public function __toString(): string {
        return implode('', (array)$this->data);
    }

    // ======== Required Interface methods ======== \\

}
