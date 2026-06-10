<?php
declare(strict_types=1);

namespace fan\core\service\cache;
use fan\core\service\cache;

/**
 * Memcache cache engine
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
    private ?object $keeperState = null;

    private mixed $arrayValueReader = null;

    public function __construct(
        cache $facade,
        string $type,
        string $key,
        array $config,
        ?object $errorLogger = null,
        ?object $runtime = null,
        ?object $keeperState = null,
        ?callable $payloadEncoder = null,
        ?callable $payloadDecoder = null,
        ?callable $jsonPayloadChecker = null,
        private mixed $configFatalExceptionFactory = null,
        ?callable $arrayValueReader = null,
        private mixed $memcacheKeeperFactory = null,
        private mixed $memcacheAvailabilityChecker = null
    )
    {
        parent::__construct(
            $facade,
            $type,
            $key,
            $config,
            $errorLogger,
            $runtime,
            $payloadEncoder,
            $payloadDecoder,
            $jsonPayloadChecker
        );
        $this->keeperState = $keeperState;
        $this->arrayValueReader = $arrayValueReader;
    }

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
     * @throws \Throwable
     */
    protected function _getKeeper(): object
    {
        $type = (string)$this->type;
        $state = $this->keeperState();
        $keeper = $state->getKeeper($type);
        if ($keeper === null) {
            if (!$this->isMemcacheAvailable()) {
                throw $this->createMissingMemcacheException($type);
            }
            $keeper = ($this->memcacheKeeperFactory())();
            if (!is_object($keeper)) {
                throw new \UnexpectedValueException('Memcache keeper factory must return an object.');
            }
            $arrayValueReader = $this->arrayValueReader();
            $keeper->addServer(
                (string)$arrayValueReader($this->config, 'HOST', 'localhost'),
                (int)$arrayValueReader($this->config, 'PORT', 11211)
            );
            $state->setKeeper($type, $keeper);
        }

        return $keeper;
    }

    protected function _getKey(string $suffix): string
    {
        return $this->type . '-' . $this->key . '-' . $suffix;
    }

    private function createConfigFatalException(string $message, int $code = E_USER_ERROR, ?\Throwable $previous = null): \Throwable
    {
        if (!is_callable($this->configFatalExceptionFactory)) {
            throw new \RuntimeException('Config cache fatal exception factory is not configured for memcache engine.');
        }

        $exception = ($this->configFatalExceptionFactory)($message, $code, $previous);
        if (!$exception instanceof \Throwable) {
            throw new \UnexpectedValueException('Config cache fatal exception factory must return a throwable.');
        }

        return $exception;
    }

    private function keeperState(): object
    {
        if ($this->keeperState === null) {
            throw new \RuntimeException('Memcache keeper state is not configured for cache engine.');
        }

        return $this->keeperState;
    }

    private function arrayValueReader(): callable
    {
        if (is_callable($this->arrayValueReader)) {
            return $this->arrayValueReader;
        }

        throw new \RuntimeException('Array value reader is not configured for memcache cache engine.');
    }

    private function memcacheKeeperFactory(): callable
    {
        if (is_callable($this->memcacheKeeperFactory)) {
            return $this->memcacheKeeperFactory;
        }

        throw new \RuntimeException('Memcache keeper factory is not configured for memcache cache engine.');
    }

    private function isMemcacheAvailable(): bool
    {
        if (is_callable($this->memcacheAvailabilityChecker)) {
            return (bool)($this->memcacheAvailabilityChecker)();
        }

        return is_callable($this->memcacheKeeperFactory);
    }

    private function createMissingMemcacheException(string $type): \Throwable
    {
        $errMsg = 'Memcache doesn\'t setup there.';

        return $type === 'config' ? $this->createConfigFatalException($errMsg) : $this->createCacheFatalException($errMsg);
    }

}
