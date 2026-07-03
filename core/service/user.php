<?php

declare(strict_types=1);

namespace fan\core\service;
use \fan\project\exception\error500 as error500;
use fan\core\base\service\multi;
use fan\core\service\config\row as config_row;
use fan\core\service\user\base;

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
class user extends multi
{
    /**
     *
     */
    public const SES_NAMESPACE = 'user';

    protected ?string $userSpace = null;
    protected mixed $identifyer = null;

    /**
     * @var \fan\core\service\user\base
     */
    protected ?object $userData = null;

    /**
     * @var callable|null
     */
    private mixed $configFactory = null;

    /**
     * @var callable|null
     */
    private $userSessionFactory = null;

    /**
     * @var callable|null
     */
    private $currentUserFactory = null;

    /**
     * @var callable|null
     */
    private $userApplicationFactory = null;

    /**
     * @var callable|null
     */
    private $userErrorFactory = null;

    /**
     * @var callable|null
     */
    private $userRequestInputFactory = null;

    /**
     * @var callable|null
     */
    private $userEntityFactory = null;

    private ?object $userState = null;

    private ?\Closure $userEngineFactory = null;

    private ?\Closure $instanceKeyEncoder = null;

    private ?\Closure $snapshotEncoder = null;

    private ?\Closure $snapshotDecoder = null;

    private ?\Closure $arrayAdducer = null;

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

    public function __construct(
        mixed $identifyer,
        string $userSpace,
        ?callable $configFactory = null,
        ?callable $sessionFactory = null,
        ?callable $currentUserFactory = null,
        ?callable $applicationFactory = null,
        ?callable $errorFactory = null,
        ?callable $requestInputFactory = null,
        ?callable $entityFactory = null,
        ?callable $userEngineFactory = null,
        ?object $serviceBootstrapRuntime = null,
        ?object $serviceConfigurator = null,
        ?callable $serviceCacheFactory = null,
        ?object $userState = null,
        ?callable $instanceKeyEncoder = null,
        ?callable $snapshotEncoder = null,
        ?callable $snapshotDecoder = null,
        ?callable $arrayAdducer = null
    )
    {
        $this->userState = $userState ?? throw new \RuntimeException('User state is not configured for user service.');
        $this->userEngineFactory = \Closure::fromCallable(
            $userEngineFactory ?? static function (string $engineClass, mixed $identifyer): base {
                throw new \RuntimeException('User engine factory is not configured for user service.');
            }
        );
        $this->arrayAdducer = \Closure::fromCallable(
            $arrayAdducer ?? static function (mixed $value): array {
                throw new \RuntimeException('Array adducer is not configured for user service.');
            }
        );
        $this->instanceKeyEncoder = \Closure::fromCallable(
            $instanceKeyEncoder ?? static function (mixed $identifyer): int|string {
                throw new \RuntimeException('User instance key encoder is not configured for user service.');
            }
        );
        $this->snapshotEncoder = \Closure::fromCallable(
            $snapshotEncoder ?? static function (mixed $state): string {
                throw new \RuntimeException('Snapshot encoder is not configured for user service.');
            }
        );
        $this->snapshotDecoder = \Closure::fromCallable(
            $snapshotDecoder ?? static function (string $payload, mixed $default = null): mixed {
                throw new \RuntimeException('Snapshot decoder is not configured for user service.');
            }
        );
        $this->setUserDependencies(
            $configFactory,
            $sessionFactory,
            $currentUserFactory,
            $applicationFactory,
            $errorFactory,
            $requestInputFactory,
            $entityFactory,
            arrayAdducer: $arrayAdducer
        );
        $this->userSpace  = (string)$userSpace;
        $this->identifyer = $identifyer;

        parent::__construct(true, $serviceBootstrapRuntime, $serviceConfigurator, $serviceCacheFactory);

        $spaceConfig = $this->_getSpaceConfig();
        $engine = $this->_getEngine((string)$spaceConfig['ENGINE'], false);
        if (!is_string($engine)) {
            throw new \RuntimeException('User engine class cannot be resolved.');
        }
        $this->userData = $this->createUserEngine($engine, $identifyer);
        $this->configureUserData($spaceConfig)->load();

        $this->_subscribeForService('application', 'setAppName', [$this, 'onSetAppName']);
    }

    // ======== Main Interface methods ======== \\

    public function setUserDependencies(
        ?callable $configFactory = null,
        ?callable $sessionFactory = null,
        ?callable $currentUserFactory = null,
        ?callable $applicationFactory = null,
        ?callable $errorFactory = null,
        ?callable $requestInputFactory = null,
        ?callable $entityFactory = null,
        ?object $serviceBootstrapRuntime = null,
        ?object $serviceConfigurator = null,
        ?callable $serviceCacheFactory = null,
        ?object $userState = null,
        ?callable $instanceKeyEncoder = null,
        ?callable $snapshotEncoder = null,
        ?callable $snapshotDecoder = null,
        ?callable $arrayAdducer = null
    ): static
    {
        if ($arrayAdducer !== null) {
            $this->arrayAdducer = \Closure::fromCallable($arrayAdducer);
        }
        if ($instanceKeyEncoder !== null) {
            $this->instanceKeyEncoder = \Closure::fromCallable($instanceKeyEncoder);
        }
        if ($snapshotEncoder !== null) {
            $this->snapshotEncoder = \Closure::fromCallable($snapshotEncoder);
        }
        if ($snapshotDecoder !== null) {
            $this->snapshotDecoder = \Closure::fromCallable($snapshotDecoder);
        }
        if ($userState !== null) {
            $this->userState = $userState;
            if ($this->userSpace !== null) {
                $this->_saveInstance();
            }
        }
        $this->configFactory = $configFactory;
        $this->userSessionFactory = $sessionFactory;
        $this->currentUserFactory = $currentUserFactory;
        $this->userApplicationFactory = $applicationFactory;
        $this->userErrorFactory = $errorFactory;
        $this->userRequestInputFactory = $requestInputFactory;
        $this->userEntityFactory = $entityFactory;

        if ($serviceBootstrapRuntime !== null || $serviceConfigurator !== null || $serviceCacheFactory !== null) {
            $this->setServiceDependencies($serviceBootstrapRuntime, $serviceConfigurator, $serviceCacheFactory);
            if ($this->userSpace !== null && $this->config === null && $serviceConfigurator !== null) {
                $this->_setConfig()->resetEnabled();
            }
        }

        if ($this->userData instanceof base && $this->config !== null) {
            $this->configureUserData($this->_getSpaceConfig());
        }

        return $this;
    }

    public function setCurrent(): bool
    {
        if (empty($this->userData) || !$this->userData->isValid()) {
            return false;
        }
        if (!$this->isCurrent()) {
            $currentUsers = $this->_getCurrentUsers();
            $currentUsers[$this->userSpace] = $this;
            $this->state()->setCurrentUsers($currentUsers);
            $this->userSession()->set('currents', $currentUsers);
            if ($this->_isCorrespondApp()) {
                $this->_broadcastMessage('currentUser', $this);
            }
        }
        return true;
    }

    public function isCurrent(): bool
    {
        $curUsers = $this->_getCurrentUsers();
        return isset($curUsers[$this->userSpace]) && $curUsers[$this->userSpace] === $this;
    }

    public function setPrioritySpace(?string $appName = null): bool
    {
        if ($this->isValid()) {
            $appName = $this->_isCorrespondApp($appName);
            if (!empty($appName)) {
                $prioritySpace = $this->state()->getPrioritySpace() ?? [];
                $prioritySpace[$appName] = $this->userSpace;
                $this->state()->setPrioritySpace($prioritySpace);
                $this->userSession()->set('priority', $prioritySpace);
                return true;
            }
        }
        return false;
    }

    public function logout(): bool
    {
        if ($this->isCurrent()) {
            $currentUsers = $this->_getCurrentUsers();
            unset($currentUsers[$this->userSpace]);
            $this->state()->setCurrentUsers($currentUsers);
            $this->userSession()->set('currents', $currentUsers);
            if ($this->_isCorrespondApp()) {
                $this->userData->logout();
                $user = $this->currentUser();
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
        foreach (($this->arrayAdducer())($role) as $roleName) {
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

    public function getEngine(): ?base
    {
        return $this->userData;
    }

    public function onSetAppName(string $appName): void
    {
        if ((string)$this->userSpace === (string)$this->state()->getCurrentUserSpace() && !$this->_isCorrespondApp($appName)) {
            $this->state()->setCurrentUserSpace(null);
            $this->currentUser();
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
        $this->state()->setInstance((string)$this->userSpace, $this->getInstanceKey($this->identifyer), $this);
        return $this;
    }

    protected function _getCurrentUsers(): array
    {
        $currentUsers = $this->state()->getCurrentUsers();
        if (is_null($currentUsers)) {
            $ses = $this->userSession();
            $currentUsers = $ses->get('currents', []);
            $this->state()->setCurrentUsers($currentUsers);
            $this->state()->setPrioritySpace($ses->get('priority', []));
            foreach ($currentUsers as $k => $v) {
                $instanceKey = $this->getInstanceKey($v->identifyer);
                if ($this->state()->getInstance((string)$k, $instanceKey) === null) {
                    $this->state()->setInstance((string)$k, $instanceKey, $v);
                }
            }
        }

        return $currentUsers;
    }

    protected function _getDelegate(mixed $class): mixed
    {
        if ((string)$class === 'userData') {
            return $this->userData;
        }
        return parent::_getDelegate($class);
    }

    protected function _getSpaceConfig(): config_row
    {
        return $this->config->get(['space', $this->userSpace]);
    }

    protected function _isCorrespondApp(?string $appName = null): ?string
    {
        if (empty($appName)) {
            $appName = $this->userApplication()->getAppName();
        }
        $appName = (string)$appName;
        $spaceConfig = $this->_getSpaceConfig()->toArray();
        return in_array($appName, $spaceConfig['APPLICATIONS']) ? $appName : null;
    }

    private function userSession(): object
    {
        if ($this->userSessionFactory !== null) {
            return ($this->userSessionFactory)(self::SES_NAMESPACE, 'system');
        }

        throw new \RuntimeException('Session service is not configured for user service.');
    }

    private function currentUser(): mixed
    {
        if ($this->currentUserFactory !== null) {
            return ($this->currentUserFactory)();
        }

        throw new \RuntimeException('Current user factory is not configured for user service.');
    }

    private function userApplication(): object
    {
        if ($this->userApplicationFactory !== null) {
            return ($this->userApplicationFactory)();
        }

        throw new \RuntimeException('Application service is not configured for user service.');
    }

    private function userEntityService(): object
    {
        if ($this->userEntityFactory !== null) {
            return ($this->userEntityFactory)();
        }

        throw new \RuntimeException('Entity service is not configured for user service.');
    }

    public function createUserFatalException(string $message, int $code = E_USER_ERROR, ?\Throwable $previous = null): \Throwable
    {
        return $this->createServiceFatalException($message, $code, $previous);
    }

    private function state(): object
    {
        return $this->userState ?? throw new \RuntimeException('User state is not configured for user service.');
    }

    /**
     * @throws \Throwable
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
        throw $this->createUserFatalException('Incorrect data format "' . gettype($data) . '"');
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
        return ($this->snapshotEncoder())($this->__serialize());
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
        $data = ($this->snapshotDecoder())((string)$data, []);
        if (!is_array($data)) {
            throw new \UnexpectedValueException('User snapshot must decode to an array.');
        }

        $this->__unserialize($data);
    }

    public function __unserialize(array $data): void
    {
        $this->userSpace  = (string)$data['user_space'];
        $this->identifyer = $data['identifyer'];
        $this->userData = $this->restoreUserData($data['user_data']);
        if ($this->userState !== null && (isset($this->instanceKeyEncoder) || is_int($this->identifyer) || is_string($this->identifyer))) {
            $this->_saveInstance();
        }

        if ($this->config !== null) {
            $this->configureUserData($this->_getSpaceConfig());
        }
    }

    private function configureUserData(config_row $spaceConfig): base
    {
        $this->userData->setEngineDependencies(
            $this->userErrorFactory !== null ? ($this->userErrorFactory)() : null,
            $this->userRequestInputFactory !== null ? ($this->userRequestInputFactory)() : null,
            isset($this->snapshotEncoder) ? $this->snapshotEncoder() : null,
            isset($this->snapshotDecoder) ? $this->snapshotDecoder() : null
        );
        if (method_exists($this->userData, 'setConfigFactory') && is_callable($this->configFactory)) {
            $this->userData->setConfigFactory($this->configFactory);
        }
        if (method_exists($this->userData, 'setEntityFactory')) {
            $this->userData->setEntityFactory(fn(): object => $this->userEntityService());
        }

        return $this->userData->setFacade($this)->setConfig($spaceConfig);
    }

    private function createUserEngine(string $engineClass, mixed $identifyer): base
    {
        if (!isset($this->userEngineFactory)) {
            $this->userEngineFactory = \Closure::fromCallable(
                static function (string $engineClass, mixed $identifyer): base {
                    throw new \RuntimeException('User engine factory is not configured for user service.');
                }
            );
        }

        $engine = ($this->userEngineFactory)($engineClass, $identifyer);
        if (!$engine instanceof base) {
            throw new \UnexpectedValueException('User engine factory must return a user engine object.');
        }

        return $engine;
    }

    /**
     * Normalizes arbitrary user identifiers into a stable storage key.
     *
     * @param mixed $identifyer User identifier supplied by the caller.
     *
     * @return int|string Key that can be used in static instance maps.
     */
    private function getInstanceKey(mixed $identifyer): int|string
    {
        if (is_int($identifyer) || is_string($identifyer)) {
            return $identifyer;
        }

        return ($this->instanceKeyEncoder())($identifyer);
    }

    private function restoreUserData(mixed $userData): base
    {
        if (is_string($userData)) {
            $userData = ($this->snapshotDecoder())($userData);
        }

        if (!$userData instanceof base) {
            throw new \UnexpectedValueException('User data snapshot must decode to a user data object.');
        }

        return $userData;
    }

    private function snapshotEncoder(): callable
    {
        if (!isset($this->snapshotEncoder)) {
            $this->snapshotEncoder = \Closure::fromCallable(
                static function (mixed $state): string {
                    throw new \RuntimeException('Snapshot encoder is not configured for user service.');
                }
            );
        }

        return $this->snapshotEncoder;
    }

    private function snapshotDecoder(): callable
    {
        if (!isset($this->snapshotDecoder)) {
            $this->snapshotDecoder = \Closure::fromCallable(
                static function (string $payload, mixed $default = null): mixed {
                    throw new \RuntimeException('Snapshot decoder is not configured for user service.');
                }
            );
        }

        return $this->snapshotDecoder;
    }

    private function instanceKeyEncoder(): callable
    {
        if (!isset($this->instanceKeyEncoder)) {
            $this->instanceKeyEncoder = \Closure::fromCallable(
                static function (mixed $identifyer): int|string {
                    throw new \RuntimeException('User instance key encoder is not configured for user service.');
                }
            );
        }

        return $this->instanceKeyEncoder;
    }

    private function arrayAdducer(): callable
    {
        if (!isset($this->arrayAdducer)) {
            $this->arrayAdducer = \Closure::fromCallable(
                static function (mixed $value): array {
                    throw new \RuntimeException('Array adducer is not configured for user service.');
                }
            );
        }

        return $this->arrayAdducer;
    }


}
