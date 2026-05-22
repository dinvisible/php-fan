<?php

declare(strict_types=1);

namespace fan\core\service\user;
use fan\project\exception\service\fatal as fatalException;
/**
 * User-data engine by data from config-file
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
class config extends base
{

    /**
     * Config of Authentication Data
     * @var \fan\core\service\config\row
     */
    protected ?object $authConfig = null;

    public function makePasswordHash(string $password): string
    {
        $login = array_val($this->data, 'login', $this->identifyer);
        return $login ? md5((string)$login . $password . (string)$this->config->get('ENGINE_KEY')) : '';
    }

    // ======== Private/Protected methods ======== \\

    protected function _loadData(): bool
    {
        $this->data = [];

        $file = $this->config->get('ENGINE_SOURCE', 'auth');
        $key  = $this->config->get('ENGINE_KEY');
        if (empty($key)) {
            return false;
        }

        $this->authConfig = $this->containerService('config', (string)$file)->get((string)$key);

        $rule = $this->_getAccessRule();
        if (empty($rule)) {
            return false;
        }

        $mainRole = $this->authConfig->main_role;
        if (empty($mainRole) || !is_string($mainRole)) {
            throw new fatalException($this->facade, 'Main role isn\'t set in config-file "' . $file . '" for "' . $key . '"!');
        }

        if ((string)$this->identifyer === 'anonymous') {
            if (!empty($rule['is_anonymous'])) {
                $this->data = $this->_getAnonymousData($rule);
            }
        } else {
            $this->data = $this->_getAuthorizedData($rule);
        }
        return !empty($this->data);
    }

    protected function _getAnonymousData(\fan\core\service\config\row $rule): array
    {
        $this->isValid = true;

        $data = [
            'id'       => 'anonymous',
            'login'    => 'anonymous',
            'password' => $this->makePasswordHash(''),
            'roles'    => [],
        ];

        $data['roles'][$this->authConfig->main_role] = null;

        $this->_mergeRoles($data['roles'], $rule->add_roles);
        return $data;
    }

    protected function _getAuthorizedData(object $rule): array
    {
        $auth = $this->_getAuthentication();
        if (empty($auth)) {
            return [];
        }

        $data = [
            'id'       => $auth->login,
            'roles'    => [],
        ];
        foreach ($this->_getKeyList() as $k) {
            if (isset($auth->$k) && !isset($data[$k])) {
                $data[$k] = $auth->$k;
            }
        }

        $data['roles'][$this->authConfig->main_role] = null;

        $this->_mergeRoles($data['roles'], $rule->add_roles);
        $this->_mergeRoles($data['roles'], $auth->roles);
        return $data;
    }

    protected function _saveData(): bool
    {
        return false;
    }

    protected function _validateForSave(): bool
    {
        return false;
    }

    protected function _getAccessRule(): mixed
    {
        $keys = ['re_domain' => 'SERVER_NAME', 're_server_ip' => 'SERVER_ADDR', 're_client_ip' => 'REMOTE_ADDR'];
        if (!empty($this->authConfig['RULE'])) {
            foreach ($this->authConfig['RULE'] as $rule) {
                foreach ($keys as $k0 => $k1) {
                    if (!empty($rule[$k0]) && !preg_match((string)$rule[$k0], (string)($_SERVER[$k1] ?? ''))) {
                        continue 2;
                    }
                }
                return $rule;
            }
        }
        return null;
    }

    protected function _getAuthentication(): mixed
    {
        foreach ($this->authConfig['AUTHENTICATION'] as $auth) {
            foreach ($this->config['IDENTIFYERS'] as $v) {
                if ((string)$auth->$v === (string)$this->identifyer) {
                    return $auth;
                }
            }
        }
        return null;
    }

    protected function _mergeRoles(array &$target, mixed $source): void
    {
        if (!empty($source) && is_string($source)) {
            $rules = [$source];
        } elseif (!empty($source) && is_array($source)) {
            $rules = $source;
        } elseif (is_object($source) && method_exists($source, 'toArray')) {
            $rules = $source->toArray();
        } else {
            return;
        }

        foreach ($rules as $v) {
            $target[(string)$v] = null;
        }
    }

    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\

}
