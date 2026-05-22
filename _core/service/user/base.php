<?php

declare(strict_types=1);

namespace fan\core\service\user;
use fan\project\exception\service\fatal as fatalException;
/**
 * Basic class engine of user-data
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
 * @method string getLogin()
 * @method string getNickName()
 * @method string getFirstName()
 * @method string getPatronymic()
 * @method string getLastName()
 * @method string getTitle()
 * @method string getGender()
 * @method string getEmail()
 * @method string getPhone()
 * @method string getLocale()
 * @method string|array getAddress()
 * @method string getStatus()
 * @method string getJoinDate()
 * @method string getVisitDate()
 * @method \fan\core\service\user setLogin()     setLogin(string $login)
 * @method \fan\core\service\user setNickName()  setNickName(string $nickName)
 * @method \fan\core\service\user setFirstName() setFirstName(string $firstName)
 * @method \fan\core\service\user setLastName()  setLastName(string $lastName)
 * @method \fan\core\service\user setTitle()     setTitle(string $title)
 * @method \fan\core\service\user setGender()    setGender(string $gender)
 * @method \fan\core\service\user setEmail()     setEmail(string $email)
 * @method \fan\core\service\user setPhone()     setPhone(string $phone)
 * @method \fan\core\service\user setLocale()    setLocale(string $locale)
 * @method \fan\core\service\user setAddress()   setAddress(string|array $address)
 * @method \fan\core\service\user setStatus()    setStatus(string $status)
 * @method string getPassword()
 */
abstract class base
{
    /**
     * Service User
     * @var \fan\core\service\user
     */
    protected ?object $facade = null;

    /**
     * Row of config
     * @var \fan\core\service\config\row
     */
    protected ?object $config = null;

    /**
     * Full data of user. Used keys:
     *   'id'         => !number|string
     *   'password'   => !string
     *   'login'      => !string
     *   'nickname'   => string
     *   'first_name' => string
     *   'patronymic' => string
     *   'last_name'  => string
     *   'title'      => string
     *   'gender'     => integer
     *   'email'      => string
     *   'phone'      => string
     *   'locale'     => string
     *   'address'    => string|array
     *   'status'     => string
     *   'roles'      => !array
     *   'join_date'  => string
     *   'visit_date' => string
     *  "!" - required parameter
     * @var array
     */
    protected array $data = [];

    protected mixed $identifyer = null;

    /**
     * Flag shows is user valid:
     *  - for new user TRUE if set all identifier and password;
     *  - for exists user TRUE if check one of identifier and password;
     * @var boolean
     */
    protected bool $isValid = false;
    /**
     * This flag is TRUE if user created as new and isn't saved yet
     * @var boolean
     */
    protected bool $isNew = true;
    /**
     * This array contain modified data
     * @var array
     */
    protected array $changed = [];

    public function __construct(mixed $identifyer)
    {
        $this->identifyer = $identifyer;
    }

    // ======== Static methods ======== \\

    // ======== Main Interface methods ======== \\
    abstract public function makePasswordHash(string $password): string;

    public function setFacade(\fan\core\service\user $facade): static
    {
        if (empty($this->facade)) {
            $this->facade = $facade;
        }
        return $this;
    }

    public function setConfig(\fan\core\service\config\row $config): static
    {
        if (empty($this->config)) {
            if (empty($config)) {
                throw new fatalException($this->facade, 'User Engine has empty config!');
            }
            $this->config = $config;
/*
            if (empty($this->data)) {
                $ident = adduceToArray($this->config['IDENTIFYERS']);
                if (count($ident) == 1) {
                    $this->data[$ident[0]] = $this->identifyer;
                }
            }
 */
        }
        return $this;
    }

    // --- Getters method --- \\

    public function getId(): mixed
    {
        return array_val($this->data, 'id', $this->identifyer);
    }

    public function getFullName(bool $withTitle = true): string
    {
        $result  = $withTitle ? $this->getTitle() . ' ' : '';
        $result .= $this->getFirstName();
        $result .= ' ' . $this->getPatronymic();
        $result  = trim($result);
        $result .= ' ' . $this->getLastName();
        return trim($result);
    }

    public function getRoles(bool $force = false): array
    {
        return ($this->isValid || $force) && isset($this->data['roles']) ? $this->data['roles'] : [];
    }

    public function getAllData(): array
    {
        $result = $this->data;
        foreach ($this->_getKeyList() as $k) {
            if (!array_key_exists($k, $result)) {
                $result[$k] = null;
            }
        }
        return $result;
    }

    // --- Setters method --- \\
    public function setVisitDate(mixed $date = null): ?\fan\core\service\user
    {
        if (is_null($date)) {
            $date = date('Y-m-d');
        }
        if (!isset($this->data['visit_date']) || (string)$this->data['visit_date'] !== (string)$date) {
            $this->data['visit_date'] = $this->changed['visit_date'] = $date;
        }
        return $this->facade;
    }

    // --- Verifying/manipulation method --- \\
    public function setPassword(string $password): ?\fan\core\service\user
    {
        $hash = $this->makePasswordHash($password);
        if (!isset($this->data['password']) || (string)$this->data['password'] !== $hash) {
            $this->data['password'] = $this->changed['password'] = $hash;
        }
        $this->isValid = true;
        return $this->facade;
    }

    public function checkPassword(string $password): bool
    {
        $hash = $this->makePasswordHash($password);
        $this->isValid = !empty($this->data['password']) && (string)$this->data['password'] === $hash;

        // Log Error Authentication if it is allowed
        if (!$this->isValid && $this->config['LOG_ERR_AUTH']) {
            if (empty($this->data)) {
                $errMsg = 'Data for "' . $this->identifyer . '" isn\'t present.';
                $note   = '';
            } else {
                $errMsg = 'Error password for "' . $this->identifyer . '".';
                $note   = 'Hash: ' . $hash . "\n" . 'NS: ' . $this->facade->getUserSpace();
            }
            $errMsg .= "\nTime: " . date('Y-m-d H:i:s') . "\nClient IP: " . ($_SERVER['REMOTE_ADDR'] ?? '');
            $this->facade->getContainerService('error')->logErrorMessage($errMsg, 'Error authentication', $note);
        }

        return $this->isValid;
    }

    public function load(): ?\fan\core\service\user
    {
        $this->isValid = false;
        if ($this->_loadData()) {
            $this->isNew   = false;
            $this->changed = [];
        }
        return $this->facade;
    }
    public function logout(): ?\fan\core\service\user
    {
        return $this->facade;
    }

    public function save(): ?\fan\core\service\user
    {
        if ($this->isNew) {
            $this->data['join_date'] = $this->changed['join_date'] = date('Y-m-d H:i:s');
        }

        if ($this->isChanged() && $this->_validateForSave() && $this->_saveData()) {
            $this->isNew   = false;
            $this->changed = [];
        }
        return $this->facade;
    }

    public function isValid(): bool
    {
        return $this->isValid;
    }
    public function isNew(): bool
    {
        return $this->isNew;
    }
    public function isChanged(): bool
    {
        return !empty($this->changed);
    }

    // ======== Private/Protected methods ======== \\

    abstract protected function _loadData(): bool;
    abstract protected function _saveData(): bool;
    abstract protected function _validateForSave(): bool;

    protected function _getKeyList(): array
    {
        return [
            'id',
            'password',
            'login',
            'nickname',
            'first_name',
            'patronymic',
            'last_name',
            'title',
            'gender',
            'email',
            'phone',
            'locale',
            'address',
            'status',
            'roles',
            'join_date',
            'visit_date',
        ];
    }

    protected function _set(string $key, mixed $val): ?\fan\core\service\user
    {
        if ((!isset($this->data[$key]) && !is_null($val)) || array_val($this->data, $key) !== $val) {
            $this->changed[$key] = $val;
        }
        $this->data[$key] = $val;
        return $this->facade;
    }
    protected function _get(string $key): mixed
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    protected function _convCamelCase(string $str): string
    {
        $parts = preg_split('/(?<=\\w)(?=[A-Z])/', $str);
        return strtolower(implode('_', is_array($parts) ? $parts : [$str]));
    }

    // ======== The magic methods ======== \\

    /**
     * @throws fatalException
     */
    public function __call(string $method, array $args): mixed
    {
        $method = (string)$method;
        $key = $this->_convCamelCase(substr($method, 3));
        if (substr($method, 0, 3) === 'set') {
            return $this->_set($key, isset($args[0]) ? $args[0] : null);
        } elseif (substr($method, 0, 3) === 'get') {
            return $this->_get($key);
        }
        throw new fatalException($this->facade, 'Incorrect call of User Engine!');
    }

    // ======== Required Interface methods ======== \\

    public function serialize(): string
    {
        return \fan\core\adapter\safe_serializer::encodePhpSnapshot($this->__serialize());
    }

    public function __serialize(): array
    {
        return [
            'flags' => [
                'valid'   => $this->isValid,
                'new'     => $this->isNew,
                'changed' => $this->changed,
            ],
            'identifyer' => $this->identifyer,
            'data'       => $this->data,
        ];
    }

    public function unserialize(string $data): void
    {
        $data = \fan\core\adapter\safe_serializer::decodePhpSnapshot((string)$data, []);
        if (!is_array($data)) {
            throw new \UnexpectedValueException('User data snapshot must decode to an array.');
        }

        $this->__unserialize($data);
    }

    public function __unserialize(array $data): void
    {
        $this->isValid = $data['flags']['valid'];
        $this->isNew   = $data['flags']['new'];
        $this->changed = $data['flags']['changed'];

        $this->identifyer = $data['identifyer'];
        $this->data       = $this->restoreNestedData($data['data'] ?? $data['main'] ?? null);
    }

    private function restoreNestedData(mixed $data): array
    {
        if (is_array($data)) {
            return $data;
        }

        if (is_string($data)) {
            $data = \fan\core\adapter\safe_serializer::decodePhpSnapshot($data, []);
        }

        if (!is_array($data)) {
            throw new \UnexpectedValueException('User nested data snapshot must decode to an array.');
        }

        return $data;
    }

}
