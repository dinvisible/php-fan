<?php

declare(strict_types=1);

namespace fan\core\service\user;
use fan\core\base\model\row;

/**
 * User-data engine by data from entity
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
 * @version of file: 05.02.006 (20.04.2015)
 */
class entity extends base
{
    /**
     * DB row data
     * @var \fan\core\base\model\row
     */
    protected ?object $row = null;

    /**
     * Allowed Get/Set Entity-Methods
     * @var array
     */
    protected array $mapping = [];

    private mixed $entityFactory = null;

    // ======== Static methods ======== \\
    // ======== Main Interface methods ======== \\
    public function setEntityFactory(callable $entityFactory): static
    {
        $this->entityFactory = $entityFactory;

        return $this;
    }

    public function makePasswordHash(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    protected function verifyPasswordHash(string $password, string $storedHash): bool
    {
        if ((password_get_info($storedHash)['algo'] ?? null) !== null) {
            return parent::verifyPasswordHash($password, $storedHash);
        }

        return hash_equals($storedHash, $this->legacyPasswordHash($password));
    }

    protected function passwordHashNeedsUpgrade(string $storedHash): bool
    {
        return (password_get_info($storedHash)['algo'] ?? null) === null
            || parent::passwordHashNeedsUpgrade($storedHash);
    }

    private function legacyPasswordHash(string $password): string
    {
        $login = $this->arrayValueReader()($this->data, 'login', $this->identifyer);

        return $login ? md5((string)$login . $password . (string)$this->config->get('ENGINE_KEY')) : '';
    }

    // ======== Private/Protected methods ======== \\

    protected function _loadData(): bool
    {
        $conf = $this->config;
        $this->row = null;
        foreach ($conf->get('IDENTIFYERS') as $v) {
            $row = $this->entityService()->get((string)$conf->get('ENGINE_KEY'))->getRowByParam([$v => $this->identifyer]);
            if ($row->checkIsLoad()) {
                $this->row  = $row;
                $this->data = $this->_getEntityData();
                return true;
            }
        }
        return false;
    }

    protected function _saveData(): bool
    {
        $row = $this->_getRow();
        if (!empty($row) && $this->isChanged()) {
            $methods = $this->_getMethodList('Setting');
            if (!empty($methods)) {
                foreach ($this->changed as $k => $v) {
                    if (!empty($methods[$k])) {
                        $method = $methods[$k];
                        $row->$method($v);
                    }
                }
                $row->save();
            }
            return true;
        }
        return false;
    }

    protected function _validateForSave(): bool
    {
        $row  = $this->_getRow();
        return !empty($row);
    }

    protected function _getEntityData(): array
    {
        $methods = $this->_getMethodList('Getting');
        if (!empty($methods)) {
            $required = ['id' => 0, 'password' => 0, 'login' => 0, 'roles' => 0];
            if (count(array_intersect_key($methods, $required)) < 4) {
                throw $this->createUserFatalException('Required keys "' . implode('", "', array_keys($required)) . '" are not get by method "getGettingMap".');
            }
        }

        $data = [];
        $row  = $this->_getRow();
        if (empty($methods)) {
            foreach ($this->_getKeyList() as $k) {
                $v = 'get_' . $k;
                $data[$k] = $row->$v(null, false);
            }
        } else {
            foreach ($methods as $k => $v) {
                $data[$k] = $row->$v();
            }
        }

        return $data;
    }

    protected function _getMethodList(string $type): ?array
    {
        if (!in_array($type, ['Getting', 'Setting'])) {
            throw $this->createUserFatalException('Incorrect type of mapping "' . $type . '".');
        }

        while (!isset($this->mapping[$type])) {
            $row = $this->_getRow();
            if (empty($row)) {
                return null;
            }

            $method = 'get' . $type . 'Map';
            $keys   = array_flip($this->_getKeyList());
            if (method_exists($row, $method)) {
                $map = $row->$method($type);
            } elseif ($type === 'Getting') {
                $map = null;
            } else {
                $err  = 'Method for mapping User-data "' . $this->className($row) . '::' . $method . '()" isn\'t set.' . "\n";
                $err .= 'Keys: ("' . implode('", "', array_keys($keys)) . '").';
                throw $this->createUserFatalException($err);
            }

            $this->mapping[$type] = empty($map) ? [] : array_intersect_key($this->arrayAdducer()($map), $keys);
        }

        return $this->mapping[$type];
    }

    protected function _getRow(): ?row
    {
        if (empty($this->row)) {
            $ettKey = $this->config->get('ENGINE_KEY');
            if ($this->isNew) {
                $this->row = $this->entityService()->get((string)$ettKey)->getNewRow();
            } elseif (!empty($this->data['id'])) {
                $this->row = $this->entityService()->get((string)$ettKey)->getRowById($this->data['id']);
            } else {
                return null;
            }
        } elseif (!$this->row->checkIsLoad() && !$this->isNew) {
            return null;
        }
        return $this->row;
    }

    private function entityService(): object
    {
        if (is_callable($this->entityFactory)) {
            return ($this->entityFactory)();
        }

        throw new \RuntimeException('Entity service is not configured for user entity engine.');
    }

    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\

}
