<?php
declare(strict_types=1);

namespace fan\core\service;
use fan\project\exception\error500 as error500;
use fan\core\base\service\multi;
use fan\core\service\cache\base;
use fan\core\service\cache\memcache;

/**
 * Cache service
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
class cache extends multi
{
    /**
     * Type of cache for config
     */
    public const CONFIG_TYPE = 'config';

    /**
     * Current cached data
     * @var string
     */
    protected ?string $type = null;

    /**
     * Engines of current cache type
     * @var \fan\core\service\cache\base[]
     */
    protected ?array $engine = null;

    /**
     * Configuration of Cache-config
     * @var array
     */
    protected ?array $configCache = null;

    private ?object $runtime = null;

    private ?\Closure $errorFactory = null;

    private ?object $cacheState = null;

    private ?object $memcacheState = null;

    private mixed $cacheEngineFactory = null;

    private ?object $sourceFileMetadata = null;

    private ?\Closure $configCacheFatalExceptionFactory = null;

    public function __construct(
        string $type,
        ?object $runtime = null,
        ?callable $errorFactory = null,
        ?object $cacheState = null,
        ?object $memcacheState = null,
        ?callable $cacheEngineFactory = null,
        ?object $serviceBootstrapRuntime = null,
        ?object $serviceConfigurator = null,
        ?callable $serviceCacheFactory = null,
        ?object $sourceFileMetadata = null,
        ?callable $configCacheFatalExceptionFactory = null
    )
    {
        $this->runtime = $runtime;
        $this->errorFactory = \Closure::fromCallable(
            $errorFactory ?? static function (): object {
                throw new \RuntimeException('Error service factory is not configured for cache service.');
            }
        );
        $this->cacheState = $cacheState;
        $this->memcacheState = $memcacheState;
        $this->cacheEngineFactory = $cacheEngineFactory;
        $this->sourceFileMetadata = $sourceFileMetadata;
        $this->configCacheFatalExceptionFactory = \Closure::fromCallable(
            $configCacheFatalExceptionFactory ?? static function (
                string $message,
                int $code = E_USER_ERROR,
                ?\Throwable $previous = null
            ): \Throwable {
                throw new \RuntimeException('Config cache fatal exception factory is not configured for cache service.');
            }
        );
        $state = $this->state();
        $type = (string)$type;
        if ($type === self::CONFIG_TYPE) {
            $this->configCache = $this->runtime()->getConfigCache();
            if (empty($this->configCache)) {
                throw $this->createConfigCacheFatalException('Config Cache in bootstrap isn\'t defined.');
            }
        } else {
            parent::__construct(!$state->hasInstances(), $serviceBootstrapRuntime, $serviceConfigurator, $serviceCacheFactory);
        }
        if ($state->getInstance($type) === null) {
            $state->setInstance($type, $this);
        }
        $this->type = $type;
    }

    private function runtime(): object
    {
        if ($this->runtime !== null) {
            return $this->runtime;
        }

        throw new \RuntimeException('Bootstrap runtime service is not configured for cache service.');
    }

    // ======== Main Interface methods ======== \\

    /**
     * @param mixed $default Fallback value returned when no explicit value is available.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->getEngine($key)->get($default);
    }

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    public function set(string $key, mixed $value, bool $autoSave = true): static
    {
        $this->getEngine($key)->set($value, $autoSave);
        return $this;
    }

    /**
     * @param mixed $callBack Callable invoked to complete the delegated operation.
     *
     * @throws \fan\project\exception\service\fatal
     */
    public function getOrDefine(string $key, mixed $callBack, bool $autoSave = true): mixed
    {
        $engine = $this->getEngine($key);
        if ($engine->isLoaded()) {
            $result = $engine->get();
        } elseif (is_callable($callBack)) {
            $result = $callBack($key);
            $engine->set($result, $autoSave);
        } else {
            throw $this->createServiceFatalException('Callback for cache is not callable.');
        }
        return $result;
    }

    public function getMeta(string $key, bool $loadMetaOnly = false): array
    {
        return $this->getEngine($key)->getMeta($loadMetaOnly);
    }

    public function getExtraMeta(string $key, string $param): mixed
    {
        return $this->getEngine($key)->getExtraMeta($param);
    }

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    public function setExtraMeta(string $key, string $param, mixed $value): static
    {
        $this->getEngine($key)->setExtraMeta($param, $value);
        return $this;
    }

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    public function checkExtraMeta(string $key, string $param, mixed $value, string $method = 'equal', bool $allowDelete = false): bool
    {
        $srcValue = $this->getExtraMeta($key, $param);
        if (is_null($srcValue)) {
            return true;
        }
        $normalizedValue = is_scalar($value) || $value === null ? (string)$value : $value;
        $normalizedSrcValue = is_scalar($srcValue) || $srcValue === null ? (string)$srcValue : $srcValue;

        switch ($method) {
        case 'equal':
            $result = $normalizedValue === $normalizedSrcValue;
            break;

        case 'not_equal':
            $result = $normalizedValue !== $normalizedSrcValue;
            break;

        case 'less':
            $result = $value < $srcValue;
            break;

        case 'more':
            $result = $value > $srcValue;
            break;

        case 'less_or_equal':
            $result = $value <= $srcValue;
            break;

        case 'more_or_equal':
            $result = $value >= $srcValue;
            break;

        default:
            throw $this->createServiceFatalException('Incorrect check method "' . $method . '", for verify Extra Meta.');
        }

        if (!$result && $allowDelete) {
            $this->delete($key);
        }
        return $result;
    }

    public function isActual(string $key): bool
    {
        return $this->getEngine($key)->isActual();
    }

    public function setLifetime(string $key, int $time): static
    {
        $this->getEngine($key)->setLifetime($time);
        return $this;
    }

    public function setStartLimit(string $key, string $dateTime): bool
    {
        $this->getEngine($key)->setStartLimit($dateTime);
        return $this->isActual($key);
    }

    /**
     * @throws \fan\project\exception\service\fatal
     */
    public function checkSourceFile(string $key, string $filePath): bool
    {
        if (!$this->sourceFileMetadata()->isFile($filePath)) {
            throw $this->createServiceFatalException('Incorrect path to  file "' . $filePath . '".');
        }
        return  $this->checkExtraMeta($key, 'file_size', $this->sourceFileMetadata()->size($filePath), 'equal', true) &&
                $this->setStartLimit($key, date ('Y-m-d H:i:s', (int)$this->sourceFileMetadata()->modifiedTime($filePath)));
    }

    public function save(string $key): static
    {
        $this->getEngine($key)->save();
        return $this;
    }

    public function delete(string $key): static
    {
        $this->getEngine($key)->delete();
        return $this;
    }

    public function setExtraPath(string $key, string $extraPath): static
    {
        $this->getEngine((string)$key)->setExtraPath((string)$extraPath);
        return $this;
    }

    public function isSaved(string $key): bool
    {
        return $this->getEngine($key)->isSaved();
    }

    public function createCacheFatalException(string $message, int $code = E_USER_ERROR, ?\Throwable $previous = null): \Throwable
    {
        if ($this->type === self::CONFIG_TYPE) {
            return $this->createConfigCacheFatalException($message, $code, $previous);
        }

        return $this->createServiceFatalException($message, $code, $previous);
    }

    public function getEngine(string $key): base
    {
        if (!isset($this->engine[$key])) {
            if ($this->type === self::CONFIG_TYPE) {
                $config = $this->configCache;
            } elseif (($config = $this->getConfig(['TYPE', $this->type]))) {
                $config = $config->toArray();
            } else {
                throw $this->createServiceFatalException('Not found configuration for "' . $this->type . '".');
            }

            if (empty($config['ENGINE'])) {
                throw $this->type === self::CONFIG_TYPE
                    ? $this->createConfigCacheFatalException('Cache engine isn\'t defined.')
                    : $this->createServiceFatalException('Cache engine isn\'t defined.');
            }
            $class = $this->type === self::CONFIG_TYPE
                ? $this->getConfigCacheEngineClass((string)$config['ENGINE'])
                : $this->_getEngine($config['ENGINE'], false);
            $this->engine[$key] = $this->cacheEngine((string)$class, $key, $config);
        }
        return $this->engine[$key];
    }

    private function errorLogger(): object
    {
        if (!isset($this->errorFactory)) {
            $this->errorFactory = \Closure::fromCallable(
                static function (): object {
                    throw new \RuntimeException('Error service factory is not configured for cache service.');
                }
            );
        }

        $errorLogger = ($this->errorFactory)();
        if (!is_object($errorLogger)) {
            throw new \UnexpectedValueException('Error service factory must return an object.');
        }

        return $errorLogger;
    }

    private function cacheEngine(string $class, string $key, array $config): object
    {
        if (!is_callable($this->cacheEngineFactory)) {
            throw new \RuntimeException('Cache engine factory is not configured for cache service.');
        }

        return ($this->cacheEngineFactory)(
            $class,
            $this,
            (string)$this->type,
            $key,
            $config,
            $this->errorLogger(),
            $this->runtime(),
            is_a($class, memcache::class, true) ? $this->memcacheState() : null,
            is_a($class, memcache::class, true) ? $this->configCacheFatalExceptionFactory() : null
        );
    }

    private function getConfigCacheEngineClass(string $name): ?string
    {
        $class = get_class($this) . '\\' . $name;
        if (substr($class, 0, 9) === 'fan\core\\') {
            $class = 'fan\project\\' . substr($class, 9);
        }

        return $this->runtime()->loadClass($class, true) ? '\\' . $class : null;
    }

    private function state(): object
    {
        if ($this->cacheState === null) {
            throw new \RuntimeException('Cache state is not configured for cache service.');
        }

        return $this->cacheState;
    }

    private function memcacheState(): object
    {
        if ($this->memcacheState === null) {
            throw new \RuntimeException('Memcache state is not configured for cache service.');
        }

        return $this->memcacheState;
    }

    private function sourceFileMetadata(): object
    {
        if ($this->sourceFileMetadata === null) {
            throw new \RuntimeException('Source file metadata adapter is not configured for cache service.');
        }

        return $this->sourceFileMetadata;
    }

    private function configCacheFatalExceptionFactory(): callable
    {
        if (!isset($this->configCacheFatalExceptionFactory)) {
            $this->configCacheFatalExceptionFactory = \Closure::fromCallable(
                static function (
                    string $message,
                    int $code = E_USER_ERROR,
                    ?\Throwable $previous = null
                ): \Throwable {
                    throw new \RuntimeException('Config cache fatal exception factory is not configured for cache service.');
                }
            );
        }

        return $this->configCacheFatalExceptionFactory;
    }

    private function createConfigCacheFatalException(
        string $message,
        int $code = E_USER_ERROR,
        ?\Throwable $previous = null
    ): \Throwable {
        $exception = ($this->configCacheFatalExceptionFactory())($message, $code, $previous);
        if (!$exception instanceof \Throwable) {
            throw new \UnexpectedValueException('Config cache fatal exception factory must return a throwable.');
        }

        return $exception;
    }


    // ======== Private/Protected methods ======== \\

    // ======== The magic methods ======== \\

    // ======== Required Interface methods ======== \\

}
