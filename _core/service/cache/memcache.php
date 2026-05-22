<?php
declare(strict_types=1);

namespace fan\core\service\cache;
use fan\project\exception\service\fatal as fatalException;
/**
 * ADOdb wrapper for template engine
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
class memcache extends base
{
    /**
     * Keepers of Memcache
     * @var string
     */
    private static array $keepers = [];

    protected function _loadData(bool $loadMetaOnly): bool
    {
        $keeper         = $this->_getKeeper();
        $metaData       = $keeper->get($this->_getKey('meta'));
        $this->metaData = !$metaData ? [] : $metaData;
        if (!$this->_checkActual($this->metaData) || $loadMetaOnly) {
            return false;
        }

        $this->data = $keeper->get($this->_getKey('data'));
        return true;
    }

    protected function _saveData(): static
    {
        $keeper = $this->_getKeeper();
        $keeper->set($this->_getKey('meta'), $this->metaData, 0, (int)$this->metaData['lifetime']);
        $keeper->set($this->_getKey('data'), $this->data,     0, (int)$this->metaData['lifetime']);
        return $this;
    }

    protected function _deleteData(): static
    {
        $keeper = $this->_getKeeper();
        $keeper->delete($this->_getKey('meta'));
        $keeper->delete($this->_getKey('data'));
        parent::_deleteData();
        return $this;
    }

    /**
     * @throws fatalException
     */
    protected function _getKeeper(): object
    {
        if (empty(self::$keepers[$this->type])) {
            if (!class_exists('\Memcache')) {
                $errMsg = 'Memcache doesn\'t setup there.';
                if ($this->type === 'config') {
                    throw new \fan\core\exception\fatal($errMsg);
                } else {
                    throw new fatalException($this->facade, $errMsg);
                }
            }
            self::$keepers[$this->type] = new \Memcache();
            self::$keepers[$this->type]->addServer(
                (string)array_val($this->config, 'HOST', 'localhost'),
                (int)array_val($this->config, 'PORT', 11211)
            );
        }
        return self::$keepers[$this->type];
    }

    protected function _getKey(string $suffix): string
    {
        return $this->type . '-' . $this->key . '-' . $suffix;
    }

}
