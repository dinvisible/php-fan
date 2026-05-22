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
class ini extends base
{
    /**
     * File extention
     * @var string
     */
    protected string $fileExtention = 'ini';

    protected function _loadSourceData(string $srcFilePath): array
    {
        $arrData = parse_ini_file($srcFilePath, true) ?: [];
        $this->_separateByDot($arrData);
        return $arrData;
    }

    protected function _separateByDot(array &$branch): void
    {
        if (is_array($branch)) {
              foreach ($branch as $k => $v) {
                $r =& $this->_checkDotSeparatedElm($branch, $k, $v);
                if (is_string($r) && substr($r, 0, 1) === '[' && substr($r, -1) === ']') {
                    $tmp = explode(';', substr($r, 1, -1));
                    $r = array_map('trim', $tmp);
                } else if (is_array($r)) {
                    $this->_separateByDot($r);
                }
            }
        }
    }

    protected function &_checkDotSeparatedElm(array &$branch, string $key, mixed $val): mixed
    {
        $dp = strpos($key, '.');
        if ($dp) {
            $key1 = substr($key, 0, $dp);
            $key2 = substr($key, $dp + 1);
            $branch[$key1][$key2] = $branch[$key];
            unset($branch[$key]);
            return $this->_checkDotSeparatedElm($branch[$key1], $key2, $val);
        }
        return $branch[$key];
    }
}
