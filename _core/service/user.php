<?php

declare(strict_types=1);

namespace fan\core\service;
use fan\project\exception\service\fatal as fatalException;
use \fan\project\exception\error500 as error500;
/**
 * user manager service
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
 * @method mixed getId()
 * @method string getLogin()
 * @method string getNickName()
 * @method string getFullName()
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
 * @method array|object getAllData()
 * @method \fan\core\service\user setLogin()      setLogin(string $login)
 * @method \fan\core\service\user setNickName()   setNickName(string $nickName)
 * @method \fan\core\service\user setFirstName()  setFirstName(string $firstName)
 * @method \fan\core\service\user setPatronymic() setPatronymic(string $patronymic)
 * @method \fan\core\service\user setLastName()   setLastName(string $lastName)
 * @method \fan\core\service\user setTitle()      setTitle(string $title)
 * @method \fan\core\service\user setGender()     setGender(string $gender)
 * @method \fan\core\service\user setEmail()      setEmail(string $email)
 * @method \fan\core\service\user setPhone()      setPhone(string $phone)
 * @method \fan\core\service\user setLocale()     setLocale(string $locale)
 * @method \fan\core\service\user setAddress()    setAddress(string|array $address)
 * @method \fan\core\service\user setStatus()     setStatus(string $status)
 * @method \fan\core\service\user setVisitDate()  setVisitDate(string $date)
 * @method \fan\core\service\user setPassword()   setPassword(string $password)
 * @method string getPassword()
 * @method boolean checkPassword(string $password)
 * @method \fan\core\service\user load()
 * @method \fan\core\service\user save()
 * @method boolean isValid()
 * @method boolean isNew()
 * @method boolean isChanged()
 */
class user extends \fan\core\base\service\multi
{
    /**
     *
     */
    public const SES_NAMESPACE = 'user';

    private static array $instances = [];

    /**
     * @var \fan\core\service\session
     */
    private static ?object $session = null;

    /**
     * @var \fan\core\service\user[]
     */
    private static ?array $currentUsers = null;

    /**
     * Priority User Space for application
     * @var array
     */
    private static ?array $prioritySpace = null;

    /**
     * Current user space resolved for the running application.
     * @var string
     */
    private static ?string $currentUserSpace = null;

    protected ?string $userSpace = null;
    protected mixed $identifyer = null;

    /**
     * @var \fan\core\service\user\base
     */
    protected ?object $userData = null;

    protected array $delegateRule = [
        'userData' => [
            // --- Getters method --- \\
            'getId',                                                     // Main identifier
            'getLogin',    'getNickName',  'getFullName', 'getFirstName', 'getPatronymic', 'getLastName', // Name data
            'getTitle',    'getGender',                                  // Personal data
            'getEmail',    'getPhone',     'getLocale',   'getAddress',  // Contact data
            'getStatus',   /*getRoles*/                                  // Status-role data
            'getJoinDate', 'getVisitDate',                               // Rating-visit data
            'getAllData',                                                // All above and another data

            // --- Setters method --- \\
            'setLogin',     'setNickName', 'setFirstName', 'setPatronymic', 'setLastName', // Name data
            'setTitle',     'setGender',                                  // Personal data
            'setEmail',     'setPhone',    'setLocale',    'setAddress',  // Contact data
            'setStatus',    /*setRoles*/   /*addRole*/     /*removeRole*/ // Status-role data
            'setVisitDate',                                               // Rating-visit data

            // --- Verifying/manipulation method --- \\
            'setPassword', 'getPassword', 'checkPassword',
            'load',        'save',
            'isValid',     'isNew',       'isChanged',
        ],
    ];

    protected function __construct(mixed $identifyer, string $userSpace)
    {
        $this->userSpace  = (string)$userSpace;
        $this->identifyer = $identifyer;

        parent::__construct();

        $spaceConfig = $this->_getSpaceConfig();
        $engine = $this->_getEngine((string)$spaceConfig['ENGINE'], false);
        $this->userData = new $engine($identifyer);
        $this->userData->setFacade($this)->setConfig($spaceConfig)->load();

        $this->_subscribeForService('application', 'setAppName', [$this, 'onSetAppName']);
    }


    // ======== Static methods ======== \\
    public static function instance(mixed $identifyer, ?string $reqSpace = null): static
    {
        $userSpace = self::_verifySpace($reqSpace);
        $instanceKey = self::_getInstanceKey($identifyer);
        if (is_null(self::$currentUsers)) {
            self::_getCurrentUsers(); // If first call - pull users from session
        }

        if (!isset(self::$instances[$userSpace][$instanceKey])) {
            new \fan\project\service\user($identifyer, $userSpace);
        }
        return self::$instances[$userSpace][$instanceKey];
    }

    public static function checkLogout(): ?\fan\core\service\user
    {
        $user = self::getCurrent();
        if (!empty($user)) {
            $field = $user->getConfig('LOGOUT_FIELD');
            if (!empty($field)) {
                $order  = (string)$user->getConfig('LOGOUT_ORDER', 'GP');
                $logout = self::staticContainerService('request')->get((string)$field, $order);
                if (!empty($logout)) {
                    for ($i = 0; $i < 100 && !empty($user); $i++) {
                        $user->logout();
                        $user = self::getCurrent();
                    }
                    if ($i > 99) {
                        throw new error500('Too many iteration for logout user.');
                    }
                    return null;
                }
            }
        }
        return $user;
    }

    public static function getCurrent(?string $reqSpace = null): ?\fan\core\service\user
    {
        $userSpace = self::_verifySpace($reqSpace);
        $curUsers  = self::_getCurrentUsers();
        return isset($curUsers[$userSpace]) ? $curUsers[$userSpace] : null;
    }

    /**
     * @throws error500
     */
    public static function getCurrentSpace(): string
    {
        $config   = self::staticContainerService('config')->get('user');
        $appName  = self::staticContainerService('application')->getAppName();
        $curUsers = self::_getCurrentUsers();

        // If is Priority Space and has current user - use it
        if (isset(self::$prioritySpace[$appName])) {
            $prioritySp = self::$prioritySpace[$appName];
            if (isset($curUsers[$prioritySp])) {
                return $prioritySp;
            }
        }

        // Use Space with first registered user
        $firstSp  = null;
        foreach ($config->get('space', []) as $k => $v) {
            if (in_array($appName, adduceToArray($v->APPLICATIONS))) {
                if (isset($curUsers[$k])) {
                    return $k;
                } elseif (empty($firstSp)) {
                    $firstSp = $k;
                }
            }
        }

        // Use Priority or First Space for current application
        if (!empty($prioritySp)) {
            return $prioritySp;
        }
        if (!empty($firstSp)) {
            return $firstSp;
        }

        // Use Default Space if another one is not defined
        $userSpace = $config->get('DEFAULT_SPACE');
        if (empty($userSpace)) {
            throw new error500('Default user space is not set.');
        }
        return $userSpace;
    }

    // ======== Main Interface methods ======== \\

    public function setCurrent(): bool
    {
        if (empty($this->userData) || !$this->userData->isValid()) {
            return false;
        }
        if (!$this->isCurrent()) {
            self::$currentUsers[$this->userSpace] = $this;
            self::_getSession()->set('currents', self::$currentUsers);
            if ($this->_isCorrespondApp()) {
                $this->_broadcastMessage('currentUser', $this);
            }
        }
        return true;
    }

    public function isCurrent(): bool
    {
        $curUsers = self::_getCurrentUsers();
        return isset($curUsers[$this->userSpace]) && $curUsers[$this->userSpace] === $this;
    }

    public function setPrioritySpace(?string $appName = null): bool
    {
        if ($this->isValid()) {
            $appName = $this->_isCorrespondApp($appName);
            if (!empty($appName)) {
                self::$prioritySpace[$appName] = $this->userSpace;
                self::_getSession()->set('priority', self::$prioritySpace);
                return true;
            }
        }
        return false;
    }

    public function logout(): bool
    {
        if ($this->isCurrent()) {
            unset(self::$currentUsers[$this->userSpace]);
            self::_getSession()->set('currents', self::$currentUsers);
            if ($this->_isCorrespondApp()) {
                $this->userData->logout();
                $user = self::getCurrent();
                if (empty($user)) {
                    $this->_broadcastMessage('logoutUser', $this);
                } else {
                    $this->_broadcastMessage('currentUser', $user);
                }
            }
            return true;
        }
        return false;
    }

    public function getRoles(bool $force = false): array
    {
        return $this->userData->getRoles($force);
    }

    public function addRole(string $role, int|float|null $expiredTime = null): static
    {
        $curRoles = $this->getRoles(true);
        $curRoles[$role] = $expiredTime;
        return $this->setRoles($curRoles);
    }

    public function removeRole(string|array $role): static
    {
        $curRoles = $this->getRoles(true);
        foreach (adduceToArray($role) as $roleName) {
            if (array_key_exists($roleName, $curRoles)) {
                unset($curRoles[$roleName]);
            }
        }
        return $this->setRoles($curRoles);
    }

    public function setRoles(array $newRoles): static
    {
        $curRoles = $this->getRoles(true);
        $curDate  = date('Y-m-d H:i:s');

        foreach ($newRoles as $k => $v) {
            if (!is_null($v) && strcmp((string)$v, $curDate) < 0) {
                unset($newRoles[$k]);
            }
        }

        if (array_diff_assoc($newRoles, $curRoles) || array_diff_assoc($curRoles, $newRoles)) {
            $this->userData->setRoles($newRoles);
            if ($this->isCurrent()) {
                $this->_broadcastMessage('changeRoles', $this);
            }
        }
        return $this;
    }

    public function getUserSpace(): ?string
    {
        return $this->userSpace;
    }

    public function makePasswordHash(string $password): string
    {
        return $this->userData->makePasswordHash($password);
    }

    public function setData(array $data): static
    {
        foreach ($data as $k => $v) {
            $this->set((string)$k, $v);
        }
        return $this;
    }

    public function getData(): array
    {
        return $this->toArray();
    }

    public function toArray(): array
    {
        return $this->userData->getAllData();
    }

    public function getEngine(): ?\fan\core\service\user\base
    {
        return $this->userData;
    }

    public function onSetAppName(string $appName): void
    {
        if ((string)$this->userSpace === (string)self::$currentUserSpace && !$this->_isCorrespondApp($appName)) {
            self::$currentUserSpace = null;
            self::getCurrent();
        }
    }

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    public function set(string $key, mixed $value): static
    {
        list($object, $method) = $this->_checkKey('set', $key);
        if (!empty($object)) {
            $object->$method($value);
        }
        return $this;
    }
    public function get(string $key): mixed
    {
        list($object, $method) = $this->_checkKey('get', $key);
        return empty($object) ? null : $object->$method();
    }

    // ======== Private/Protected methods ======== \\

    protected function _saveInstance(): static
    {
        self::$instances[(string)$this->userSpace][self::_getInstanceKey($this->identifyer)] = $this;
        return $this;
    }

    protected static function _getCurrentUsers(): array
    {
        if (is_null(self::$currentUsers)) {
            $ses = self::_getSession();
            self::$currentUsers  = $ses->get('currents', []);
            self::$prioritySpace = $ses->get('priority', []);
            foreach (self::$currentUsers as $k => $v) {
                $instanceKey = self::_getInstanceKey($v->identifyer);
                if (!isset(self::$instances[$k][$instanceKey])) {
                    self::$instances[$k][$instanceKey] = $v;
                }
            }
        }

        return self::$currentUsers;
    }

    /**
     * @throws error500
     */
    protected static function _verifySpace(?string $userSpace): string
    {
        if (empty($userSpace)) {
            return self::getCurrentSpace();
        }
        $userSpace = (string)$userSpace;
        $config = self::staticContainerService('config');
        if (!$config->get(['user', 'space', $userSpace])) {
            throw new error500('Incorrect identifyer of user space - "' . $userSpace . '".');
        }
        return $userSpace;
    }

    protected static function _getSession(): \fan\core\service\session
    {
        if (empty(self::$session)) {
            self::$session = self::staticContainerService('session', self::SES_NAMESPACE, 'system');
        }
        return self::$session;
    }

    protected function _getDelegate(mixed $class): mixed
    {
        if ((string)$class === 'userData') {
            return $this->userData;
        }
        return parent::_getDelegate($class);
    }

    protected function _getSpaceConfig(): \fan\core\service\config\row
    {
        return $this->config->get(['space', $this->userSpace]);
    }

    protected function _isCorrespondApp(?string $appName = null): ?string
    {
        if (empty($appName)) {
            $appName = $this->containerService('application')->getAppName();
        }
        $appName = (string)$appName;
        $spaceConfig = $this->_getSpaceConfig()->toArray();
        return in_array($appName, $spaceConfig['APPLICATIONS']) ? $appName : null;
    }

    /**
     * @throws fatalException
     */
    protected function _convertToArray(mixed $data): array
    {
        if (is_array($data)) {
            return $data;
        }
        if (is_string($data)) {
            return [$data];
        }
        if (is_object($data) && method_exists($data, 'toArray')) {
            return $data->toArray();
        }
        throw new fatalException($this, 'Incorrect data format "' . gettype($data) . '"');
    }

    protected function _checkKey(string $type, string $key): array
    {
        $data = $this->userData->getAllData();
        $type = (string)$type;
        $key = (string)$key;
        if (!array_key_exists($key, $data)) {
            return [null, null];
        }

        $tmp    = array_map('ucfirst', explode('_', $key));
        $method = $type . implode('', $tmp);

        $object = method_exists($this, $method) ? $this : $this->userData;
        return [$object, $method];
    }

    // ======== The magic methods ======== \\
    /**
     * Handles dynamic property writes for this current component.
     *
     * @param mixed $value Value that should be applied or transformed.
     */
    public function __set(string $key, mixed $value): void
    {
        $this->set((string)$key, $value);
    }
    /**
     * Handles dynamic property reads for this current component.
     */
    public function __get(string $key): mixed
    {
        return $this->get((string)$key);
    }

    // ======== Required Interface methods ======== \\

    public function serialize(): string
    {
        return \fan\core\adapter\safe_serializer::encodePhpSnapshot($this->__serialize());
    }

    public function __serialize(): array
    {
        return [
            'user_space' => $this->userSpace,
            'identifyer' => $this->identifyer,
            'user_data'  => $this->userData,
        ];
    }

    public function unserialize(string $data): void
    {
        $data = \fan\core\adapter\safe_serializer::decodePhpSnapshot((string)$data, []);
        if (!is_array($data)) {
            throw new \UnexpectedValueException('User snapshot must decode to an array.');
        }

        $this->__unserialize($data);
    }

    public function __unserialize(array $data): void
    {
        $this->userSpace  = (string)$data['user_space'];
        $this->identifyer = $data['identifyer'];
        $this->_saveInstance()->_setConfig()->resetEnabled();

        $this->userData = $this->restoreUserData($data['user_data']);
        $this->userData->setFacade($this)->setConfig($this->_getSpaceConfig());
    }

    /**
     * Normalizes arbitrary user identifiers into a stable storage key.
     *
     * @param mixed $identifyer User identifier supplied by the caller.
     *
     * @return int|string Key that can be used in static instance maps.
     */
    private static function _getInstanceKey(mixed $identifyer): int|string
    {
        return is_int($identifyer) || is_string($identifyer) ?
            $identifyer :
            \fan\core\adapter\safe_serializer::stableKey($identifyer);
    }

    private function restoreUserData(mixed $userData): \fan\core\service\user\base
    {
        if (is_string($userData)) {
            $userData = \fan\core\adapter\safe_serializer::decodePhpSnapshot($userData);
        }

        if (!$userData instanceof \fan\core\service\user\base) {
            throw new \UnexpectedValueException('User data snapshot must decode to a user data object.');
        }

        return $userData;
    }


}
