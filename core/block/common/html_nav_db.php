<?php

declare(strict_types=1);

namespace fan\core\block\common;
/**
 * Base class for all kind of dynamic menu, wich formed by data base
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
 * @version of file: 02.014
 * @abstract
 */
abstract class html_nav_db extends html_nav
{

    private array $navElements = [];

    protected array $srcElements = [];

    protected function _getNav(mixed $groupKey = null): array
    {
        $rowset = $this->entityService()->getMenuElement($groupKey);

        $ret  = [];
        $chld = [];

        foreach ($rowset as $e) {
            $id = $e->getId();
            $this->navElements[$id] = $e;
            if ($this->roleService()->check($e->get___url_role())) {
                $v = $e->getFields();
                $this->srcElements[$id] = [
                    'order_key'     => $v['order_key'],
                    'condition_key' => $v['condition_key'],
                    'target'        => (string)$v['target'] === 'self' ? null : '_' . $v['target'],
                    'url_value'     => $this->getMenuURL((string)((string)$v['menu_type'] === 'local' ? $v['__url_value'] : $v['__foreign_url']), (string)$v['menu_type'], (string)$v['__protocol']),
                    'menu_name'     => $v['__menu_name'],
                    'current'       => $this->checkCurrentElement((string)$v['menu_key']),
                    'children'      => [],
                ];
                if ($v['id_menu_element_parent'] && (string)$v['id_menu_element_parent'] !== (string)$v['id_menu_element']) {
                    $chld[$id] =& $this->srcElements[$id];
                    $chld[$id]['parent'] = $v['id_menu_element_parent'];
                } else {
                    $ret[$id] =& $this->srcElements[$id];
                }
            }
        }

        foreach ($chld as $id => &$v) {
            $this->srcElements[$v['parent']]['children'][] =& $v;
        }
        return $ret;
    }

    protected function _getNavName(string $key = 'group_key'): string|false
    {
        $menuGroup = $this->entityService()->getMenuGroup($key);
        if ($menuGroup->checkIsLoad()) {
            return $menuGroup->group_name;
        }
        return false;
    }

    protected function _getNavRow(int|float $id): ?object
    {
        return isset($this->navElements[$id]) ? $this->navElements[$id] : null;
    }

}
