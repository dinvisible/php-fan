<?php

declare(strict_types=1);

namespace fan\core\block\common;
use fan\core\block\base;

/**
 * Base class for all kind of meta nav
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
 * @version of file: 02.025
 * @abstract
 */
abstract class html_nav extends base
{
    /**
     * Current Request
     * @var array
     */
    protected array $currentRequest = [];

    public function init(): void
    {
        $this->view->navList = $this->_getNav();
    }

    protected function _getNav(mixed $key = 'nav'): array
    {
        return $this->_parseNav((array)$this->getMeta($key, [], true));
    }

    protected function _parseNav(array $nav): array
    {
        $result = [];
        $arrayValueReader = $this->arrayValueReader();
        foreach ($nav as $k => $v){
            if (!isset($v['role']) || $this->roleService()->check($v['role'])) {
                $result[$k] = [
                    'nav_name'  => $arrayValueReader($v, 'nav_name', '&nbsp;'),
                    'url_value' => $this->_getNavURI((string)$v['url_value'], (string)$arrayValueReader($v, 'url_type', 'local'), (string)$arrayValueReader($v, 'protocol', '')),
                    'current'   => isset($v['nav_key'])   ? $this->_checkCurrentElement((string)$v['nav_key']) : false,
                    'children'  => !empty($v['children']) ? $this->_parseNav($v['children']) : [],
                ];
            }
        }
        return $result;
    }

    protected function _getNavURI(string $url, string $type = 'local', ?string $protocol = null): string
    {
        if ($type === 'dummy') {
            return '#';
        }
        if ($type === 'foreign') {
            return $url;
        }
        return $this->tab->getURI($url, 'link', null, $protocol);
    }

    protected function _checkCurrentElement(string $key): bool
    {
        $request = $this->_getCurrentRequest();
        $ret = false;
        foreach (explode(',', $key) as $v) {
            [$k1, $k2] = array_pad(explode(':', $v, 2), 2, null);
            if (!$k2) {
                $k2 = trim($k1);
                $k1 = 0;
            } else {
                $k1 = (int)trim($k1);
                $k2 = trim($k2);
            }
            if (isset($request[$k1])) {
                if ((string)$request[$k1] !== $k2) {
                    return false;
                }
                $ret = true;
            }
        }
        return $ret;
    }

    protected function _getCurrentRequest(bool $force = false): array
    {
        if (empty($this->currentRequest) || $force) {
            $curReq  = $this->tab->getCurrentURI(false, false, false, true);
            $request = explode('/', trim($curReq, '/'));
            if (($request[0] ?? '') === 'static_pages') {
                array_shift($request);
            }
            if ($this->getMeta('allowUrlPrefix', false)) {
                $matcher = $this->matcherService();
                /* @var $matcher \fan\core\service\matcher */
                $prefix = (string)$matcher->getCurrentItem()->parsed->app_prefix;
                if ($prefix) {
                    foreach (explode('/', $prefix) as $v) {
                        if ($v) {
                            array_unshift($request, $v);
                        }
                    }
                }
            }
            $this->currentRequest = $request;
        }
        return $this->currentRequest;
    }

}
