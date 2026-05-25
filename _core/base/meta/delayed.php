<?php

declare(strict_types=1);

namespace fan\core\base\meta;
/**
 * Class for get delayed meta-data, after make block
 *
 * This file is part PHP-FAN (php-framework of Alexandr Nosov)
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
class delayed
{
    protected mixed $obj = null;
    protected ?string $method = null;
    protected ?array $arguments = null;

    public function __construct(object|string $obj, string $method, mixed $arguments)
    {
        $this->obj       = $obj;
        $this->method    = $method;
        $this->arguments = is_null($arguments) ? [] : (is_array($arguments) ? $arguments : [$arguments]);
    }

    public function getValue(): mixed
    {
        $callable = [$this->obj, $this->method];

        return $callable(...$this->arguments);
    }

}
