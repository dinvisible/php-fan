<?php

declare(strict_types=1);

namespace fan\core\service\user;
use fan\core\service\config\row;
use fan\core\service\user;

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

    private ?object $errorLogger = null;

    private ?object $requestInput = null;

    private ?\Closure $snapshotEncoder = null;

    private ?\Closure $snapshotDecoder = null;

    private ?\Closure $arrayValueReader = null;

    private ?\Closure $arrayAdducer = null;

    private ?\Closure $classNameResolver = null;

    public function __construct(
        mixed $identifyer,
        ?callable $snapshotEncoder = null,
        ?callable $snapshotDecoder = null,
        ?callable $arrayValueReader = null,
        ?callable $arrayAdducer = null,
        ?callable $classNameResolver = null
    )
    {
        $this->identifyer = $identifyer;
        $this->snapshotEncoder = \Closure::fromCallable(
            $snapshotEncoder ?? static function (mixed $state): string {
                throw new \RuntimeException('Snapshot encoder is not configured for user engine.');
            }
        );
        $this->snapshotDecoder = \Closure::fromCallable(
            $snapshotDecoder ?? static function (string $payload, mixed $default = null): mixed {
                throw new \RuntimeException('Snapshot decoder is not configured for user engine.');
            }
        );
        $this->arrayValueReader = \Closure::fromCallable(
            $arrayValueReader ?? static function (array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed {
                throw new \RuntimeException('Array value reader is not configured for user engine.');
            }
        );
        $this->arrayAdducer = \Closure::fromCallable(
            $arrayAdducer ?? static function (mixed $value): array {
                throw new \RuntimeException('Array adducer is not configured for user engine.');
            }
        );
        $this->classNameResolver = \Closure::fromCallable(
            $classNameResolver ?? static function (object $object): string {
                throw new \RuntimeException('Class name resolver is not configured for user engine.');
            }
        );
    }

    // ======== Static methods ======== \\

    // ======== Main Interface methods ======== \\
    abstract public function makePasswordHash(string $password): string;

    public function setEngineDependencies(
        ?object $errorLogger = null,
        ?object $requestInput = null,
        ?callable $snapshotEncoder = null,
        ?callable $snapshotDecoder = null,
        ?callable $arrayValueReader = null,
        ?callable $arrayAdducer = null,
        ?callable $classNameResolver = null
    ): static
    {
        $this->errorLogger = $errorLogger;
        $this->requestInput = $requestInput;
        if ($snapshotEncoder !== null) {
            $this->snapshotEncoder = \Closure::fromCallable($snapshotEncoder);
        }
        if ($snapshotDecoder !== null) {
            $this->snapshotDecoder = \Closure::fromCallable($snapshotDecoder);
        }
        if ($arrayValueReader !== null) {
            $this->arrayValueReader = \Closure::fromCallable($arrayValueReader);
        }
        if ($arrayAdducer !== null) {
            $this->arrayAdducer = \Closure::fromCallable($arrayAdducer);
        }
        if ($classNameResolver !== null) {
            $this->classNameResolver = \Closure::fromCallable($classNameResolver);
        }

        return $this;
    }

    public function setFacade(user $facade): static
    {
        if (empty($this->facade)) {
            $this->facade = $facade;
        }
        return $this;
    }

    public function setConfig(row $config): static
    {
        if (empty($this->config)) {
            $this->config = $config;
        }
        return $this;
    }

    // --- Getters method --- \\

    public function getId(): mixed
    {
        return $this->arrayValueReader()($this->data, 'id', $this->identifyer);
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
    public function setVisitDate(mixed $date = null): ?user
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
    public function setPassword(string $password): ?user
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
        $storedHash = (string)($this->data['password'] ?? '');
        $this->isValid = $storedHash !== '' && $this->verifyPasswordHash($password, $storedHash);

        if ($this->isValid && $this->passwordHashNeedsUpgrade($storedHash)) {
            $this->setPassword($password);
        }

        // Log Error Authentication if it is allowed
        if (!$this->isValid && $this->config['LOG_ERR_AUTH']) {
            if (empty($this->data)) {
                $errMsg = 'Data for "' . $this->identifyer . '" isn\'t present.';
                $note   = '';
            } else {
                $errMsg = 'Error password for "' . $this->identifyer . '".';
                $note = 'Stored hash algorithm: ' . (password_get_info($storedHash)['algoName'] ?? 'unknown')
                    . "\n" . 'NS: ' . $this->facade->getUserSpace();
            }
            $errMsg .= "\nTime: " . date('Y-m-d H:i:s') . "\nClient IP: " . $this->requestInput()->serverValue('REMOTE_ADDR', '');
            $this->errorLogger()->logErrorMessage($errMsg, 'Error authentication', $note);
        }

        return $this->isValid;
    }

    protected function verifyPasswordHash(string $password, string $storedHash): bool
    {
        if ((password_get_info($storedHash)['algo'] ?? null) !== null) {
            return password_verify($password, $storedHash);
        }

        return hash_equals($storedHash, $this->makePasswordHash($password));
    }

    protected function passwordHashNeedsUpgrade(string $storedHash): bool
    {
        return (password_get_info($storedHash)['algo'] ?? null) !== null
            && password_needs_rehash($storedHash, PASSWORD_DEFAULT);
    }

    public function load(): ?user
    {
        $this->isValid = false;
        if ($this->_loadData()) {
            $this->isNew   = false;
            $this->changed = [];
        }
        return $this->facade;
    }
    public function logout(): ?user
    {
        return $this->facade;
    }

    public function save(): ?user
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

    protected function requestInput(): object
    {
        return $this->requestInput ?? throw new \RuntimeException('Request input service is not configured for user engine.');
    }

    protected function errorLogger(): object
    {
        return $this->errorLogger ?? throw new \RuntimeException('Error service is not configured for user engine.');
    }

    protected function createUserFatalException(string $message, int $code = E_USER_ERROR, ?\Throwable $previous = null): \Throwable
    {
        if (!method_exists($this->facade, 'createUserFatalException')) {
            throw new \RuntimeException('User fatal exception factory is not configured for user engine.');
        }

        $exception = $this->facade->createUserFatalException($message, $code, $previous);
        if (!$exception instanceof \Throwable) {
            throw new \UnexpectedValueException('User fatal exception factory must return a throwable object.');
        }

        return $exception;
    }

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

    protected function _set(string $key, mixed $val): ?user
    {
        if ((!isset($this->data[$key]) && !is_null($val)) || $this->arrayValueReader()($this->data, $key) !== $val) {
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

    public function __call(string $method, array $args): mixed
    {
        $method = (string)$method;
        $key = $this->_convCamelCase(substr($method, 3));
        if (substr($method, 0, 3) === 'set') {
            return $this->_set($key, isset($args[0]) ? $args[0] : null);
        } elseif (substr($method, 0, 3) === 'get') {
            return $this->_get($key);
        }
        throw $this->createUserFatalException('Incorrect call of User Engine!');
    }

    // ======== Required Interface methods ======== \\

    public function serialize(): string
    {
        return ($this->snapshotEncoder())($this->__serialize());
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
        $data = ($this->snapshotDecoder())((string)$data, []);
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
            $data = ($this->snapshotDecoder())($data, []);
        }

        if (!is_array($data)) {
            throw new \UnexpectedValueException('User nested data snapshot must decode to an array.');
        }

        return $data;
    }

    private function snapshotEncoder(): callable
    {
        if (!isset($this->snapshotEncoder)) {
            $this->snapshotEncoder = \Closure::fromCallable(
                static function (mixed $state): string {
                    throw new \RuntimeException('Snapshot encoder is not configured for user engine.');
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
                    throw new \RuntimeException('Snapshot decoder is not configured for user engine.');
                }
            );
        }

        return $this->snapshotDecoder;
    }

    protected function arrayValueReader(): callable
    {
        if (!isset($this->arrayValueReader)) {
            $this->arrayValueReader = \Closure::fromCallable(
                static function (array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed {
                    throw new \RuntimeException('Array value reader is not configured for user engine.');
                }
            );
        }

        return $this->arrayValueReader;
    }

    protected function arrayAdducer(): callable
    {
        if (!isset($this->arrayAdducer)) {
            $this->arrayAdducer = \Closure::fromCallable(
                static function (mixed $value): array {
                    throw new \RuntimeException('Array adducer is not configured for user engine.');
                }
            );
        }

        return $this->arrayAdducer;
    }

    protected function className(object $object): string
    {
        if (!isset($this->classNameResolver)) {
            $this->classNameResolver = \Closure::fromCallable(
                static function (object $object): string {
                    throw new \RuntimeException('Class name resolver is not configured for user engine.');
                }
            );
        }

        return (string)($this->classNameResolver)($object);
    }

}
