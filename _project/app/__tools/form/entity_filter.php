<?php

declare(strict_types=1);

namespace fan\app\__tools\form;
/**
 * entity_filter block
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
 * @version of file: 05.02.005 (12.02.2015)
 */
class entity_filter extends \fan\project\block\form\filter
{
    public function init(): void
    {
        $this->_parseForm(true, true);
    }

    public function getDbList(): array
    {
        $db   = [];
        $conf = $this->containerService('database')->getConfig();
        foreach ($conf['DATABASE'] as $k => $v) {
            $db[] = [
                'text'  => $v['DATABASE'],
                'value' => $k,
            ];
        }
        return $db;
    }

    public function getDirList(): array
    {
        $prefix = rtrim($this->containerService('entity')->getNsPrefix(), '\\');
        $text   = str_replace('\\', '/', $prefix);
        $dir = [[
            'text'  => $text,
            'value' => $prefix,
        ]];
        $sep  = \fan\core\bootstrap\loader::DEFAULT_DIR_SEPARATOR;
        $path = \bootstrap::getLoader()->getPathByNS($prefix);
        foreach (scandir($path) as $v) {
            if (
                    $v !== '.' &&
                    $v !== '..' &&
                    is_dir($path . $sep . $v) &&
                    !is_file($path . $sep . $v . $sep . 'entity.php')
            ) {
                $dir[] = [
                    'text'  => $text . '/' . $v,
                    'value' => $prefix . '\\' . $v,
                ];
            }
        }
        return $dir;
    }
}
