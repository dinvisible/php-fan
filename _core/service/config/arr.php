<?php

declare(strict_types=1);

namespace fan\core\service\config;
/**
 * Description of ini
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
class arr extends base
{
    /**
     * File extention
     * @var string
     */
    protected string $fileExtention = 'php';

    /**
     * @var callable|null
     */
    private $phpArrayFileLoader = null;

    public function setPhpArrayFileLoader(callable $phpArrayFileLoader): static
    {
        $this->phpArrayFileLoader = $phpArrayFileLoader;

        return $this;
    }

    protected function _loadSourceData(string $srcFilePath): array
    {
        $data = ($this->phpArrayFileLoader())($srcFilePath, []);

        if (!is_array($data)) {
            return [];
        }

        return $data;
    }

    private function phpArrayFileLoader(): callable
    {
        if (!is_callable($this->phpArrayFileLoader)) {
            throw new \RuntimeException('PHP-array file loader is not configured for config arr loader.');
        }

        return $this->phpArrayFileLoader;
    }
}
