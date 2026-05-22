<?php
declare(strict_types=1);

namespace fan\core\service;
use fan\project\exception\service\fatal as fatalException;
use fan\project\exception\error500 as error500;
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
class cache extends \fan\core\base\service\multi
{
    /**
     * Type of cache for config
     */
    public const CONFIG_TYPE = 'config';

    /**
     * Service's Instances
     * @var \fan\core\service\cache[]
     */
    private static array $instances = [];

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

    protected function __construct(string $type)
    {
        $type = (string)$type;
        if ($type === self::CONFIG_TYPE) {
            $this->configCache = \bootstrap::getConfigCache();
            if (empty($this->configCache)) {
                throw new \Exception('Config Cache in bootstrap isn\'t defined.', E_USER_ERROR);
            }
        } else {
            parent::__construct(empty(self::$instances));
        }
        if (!isset(self::$instances[$type])) {
            self::$instances[$type] = $this;
        }
        $this->type = $type;
    }

    // ======== Static methods ======== \\
    /**
     * @throws error500
     */
    public static function configInstance(): ?static
    {
        try {
            if (!empty(self::$instances)) {
                throw new error500('It\'s inpossible to get config-Instance after make another Instances.', E_USER_ERROR);
            }
            $configCache = new self(self::CONFIG_TYPE);
            $configCache->get('service');
        } catch (\Exception $exc) {
            \bootstrap::logError($exc->getMessage());
            return null;
        }
        return self::$instances[self::CONFIG_TYPE];
    }

    /**
     * @throws fatalException
     */
    public static function instance(mixed $type = null): static
    {
        if (is_null($type)) {
            $config = self::staticContainerService('config');
            $type   = $config->get('cache')->get('DEFAULT_TYPE');
            if (empty($type)) {
                throw new fatalException($config, 'Default CACHE-type doesn\'t set in config-file.');
            }
        }
        $type = (string)$type;
        if ($type === self::CONFIG_TYPE) {
            throw new error500('It\'s inpossible to get config-Instance by usual way.', E_USER_ERROR);
        }
        if (!isset(self::$instances[$type])) {
            new self($type);
        }
        return self::$instances[$type];
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
     * @throws fatalException
     */
    public function getOrDefine(string $key, mixed $callBack, bool $autoSave = true): mixed
    {
        $engine = $this->getEngine($key);
        if ($engine->isLoaded()) {
            $result = $engine->get();
        } elseif (is_callable($callBack)) {
            $result = call_user_func($callBack, $key);
            $engine->set($result, $autoSave);
        } else {
            throw new fatalException($this, 'Callback for cache is not callable.');
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
            throw new fatalException($this, 'Incorrect check method "' . $method . '", for verify Extra Meta.');
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
     * @throws fatalException
     */
    public function checkSourceFile(string $key, string $filePath): bool
    {
        if (!is_file($filePath)) {
            throw new fatalException($this, 'Incorrect path to  file "' . $filePath . '".');
        }
        return  $this->checkExtraMeta($key, 'file_size', filesize($filePath), 'equal', true) &&
                $this->setStartLimit($key, date ('Y-m-d H:i:s', filemtime($filePath)));
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

    public function getEngine(string $key): \fan\core\service\cache\base
    {
        if (!isset($this->engine[$key])) {
            if ($this->type === self::CONFIG_TYPE) {
                $config = $this->configCache;
            } elseif (($config = $this->getConfig(['TYPE', $this->type]))) {
                $config = $config->toArray();
            } else {
                throw new fatalException($this, 'Not found configuration for "' . $this->type . '".');
            }

            if (empty($config['ENGINE'])) {
                throw new fatalException($this, 'Cache engine isn\'t defined.');
            }
            $class  = $this->_getEngine($config['ENGINE'], false);
            $this->engine[$key] = new $class($this, $this->type, $key, $config);
        }
        return $this->engine[$key];
    }


    // ======== Private/Protected methods ======== \\

    // ======== The magic methods ======== \\

    // ======== Required Interface methods ======== \\

}
