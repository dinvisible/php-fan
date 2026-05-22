<?php

declare(strict_types=1);

namespace fan\app\__log_viewer\design;
/**
 * main_nav block
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
class nav extends \fan\project\block\base
{
    public function getNavUrl(string $key, string $addUrl = ''): string
    {
        return $this->tab->getURI('/' . $key . $addUrl . '.html', 'link', null, null);
    }

    public function getVarieties(): array
    {
        $list = [];
        $tpl = $this->getMeta('tplVars');
        foreach ($tpl['nav'] as $v) {
            $list[] = $v['key'];
        }
        return $list;
    }

}
