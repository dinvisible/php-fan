<?php

declare(strict_types=1);

namespace fan\core\service;
use fan\project\exception\service\fatal as fatalException;
/**
 * Entity manager service
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
class entity extends \fan\core\base\service\multi
{
    private static array $instances = [];

    private array $entities = [];

    protected mixed $collection = null;

    /**
     * @throws \fan\project\exception\service\fatal
     */
    protected function __construct(int|float|string|bool $collection = 0)
    {
        parent::__construct();

        if (is_null($collection)) {
            throw new fatalException($this, 'Collection Key can not be NULL.');
        }
        if (!is_scalar($collection)) {
            throw new fatalException($this, 'Collection Key can not be only scalar type.');
        }

        $this->collection = $collection;
        self::$instances[$collection] = $this;

        $delegate = $this->getConfig('delegate');
        if (!empty($delegate)) {
            if (!is_array_alt($delegate)) {
                throw new fatalException($this, 'Delegate list must be as array.');
            }
            foreach ($delegate as $k => $v) {
                if (!isset($this->delegateRule[$v])) {
                    $this->delegateRule[$v] = [$k];
                } else {
                    $this->delegateRule[$v][] = $k;
                }
            }
            $x = 1;
        }
    }


    // ======== Static methods ======== \\
    public static function instance(int|float|string|bool $collection = 0): static
    {
        if (!isset(self::$instances[$collection])) {
            new self($collection);
        }
        return self::$instances[$collection];
    }

    // ======== The magic methods ======== \\
    /**
     * Handles dynamic property reads for this current component.
     */
    public function __get(string $name): mixed
    {
        return $this->get((string)$name);
    }

    // ======== Main Interface methods ======== \\
    /**
     * @throws \fan\project\exception\service\fatal
     */
    public function get(string $name, array $param = []): \fan\core\base\model\entity
    {
        if (!isset($this->entities[$name])) {
            $prefix = $this->getNsPrefix();
            if (substr($name, 0, strlen($prefix)) === $prefix) {
                $class = $name . '\entity';
                $name  = substr($name, strlen($prefix));
            } else {
                $name = trim($name, '\\');
                $class = $prefix . $name . '\entity';
            }
            $this->entities[$name] = $this->_getEntity($class, $param, $name);
        }
        return $this->entities[$name];
    }

    public function getAnonymous(string $class, array $param = []): \fan\core\base\model\entity
    {
        return $this->_getEntity($class, $param);
    }

    public function getEntityByTable(string $tableName, ?string $connectionName = null, bool $force = false): ?\fan\core\base\model\entity
    {
        $name = $this->_getNameByTable($tableName, $connectionName, $force);
        if (empty($name)) {
            return null;
        }
        try {
            $ett  = $this->get($name);
        } catch (fatalException $e) {
            return null;
        }
        return $ett;
    }

    public function getSqlDir(): string
    {
        $dir = $this->_getConfigParam('SQL_DIR');
        return empty($dir) ? 'sql' : $dir;
    }

    public function getNsPrefix(): string
    {
        $prefix = $this->_getConfigParam('NS_PREFIX');
        return empty($prefix) ? '\fan\model\\' : '\\' . trim($prefix, '\\') . '\\';
    }
    public function getFileNsSuffix(): string
    {
        $suffix = $this->_getConfigParam('FILE_NS_SUFFIX');
        return empty($suffix) ? '' : trim($suffix, '\\') . '\\';
    }

    public function getCollectionKey(): mixed
    {
        return $this->collection;
    }

    public function getDescription(\fan\core\base\model\entity $entity, array $param = []): \fan\core\service\entity\description
    {
        return new \fan\project\service\entity\description($entity, $param);
    }

    /**
     * @throws \fan\project\exception\service\fatal
     */
    public function getDesigner(\fan\core\base\model\entity $entity, string $type = 'select'): \fan\core\service\entity\designer
    {
        $className = '\fan\project\service\entity\designer\\' . $type;
        if (!class_exists($className)) {
            throw new fatalException($this, 'Class of SQL-designer "' . $type . '" doesn\'t exist.');
        }
        return new $className($entity);
    }

    /**
     * @param mixed $callback Callable invoked to complete the delegated operation.
     */
    public function getSnippet(\fan\core\service\entity\designer\snippety $snippety, mixed $query, mixed $srcCondition, mixed $callback): \fan\core\service\entity\snippet
    {
        return new \fan\project\service\entity\snippet($snippety, $query, $srcCondition, $callback);
    }

    public function getEncapsulant(?string $class = null): object
    {
        $class = '\fan\project\service\entity\encapsulant\\' . ($class ? $class : (string)$this->getConfig('encapsulantClass', 'simple'));
        return new $class($this);
    }

    // ======== Private/Protected methods ======== \\
    /**
     * @throws fatalException
     */
    protected function _getEntity(string $class, array $param, ?string $name = null): \fan\core\base\model\entity
    {
        if (!class_exists($class)) {
            throw new fatalException($this, 'Undefind entity "' . $class . '"');
        }

        $entity = new $class($this, $name, $param);
        if (!$entity instanceof \fan\core\base\model\entity) {
            throw new fatalException($this, 'Entity "' . (empty($name) ? $class : $name) . '" must be instance of "\fan\core\base\model\entity"');
        }
        return $entity;
    }

    protected function _getConfigParam(string $key): mixed
    {
        $data0 = $this->getConfig($key, []);
        $extraConf = $this->getConfig(['COLLECTION', $this->getCollectionKey()]);
        $data1 = is_object($extraConf) ? $extraConf->get($key) : null;
        return empty($data1) ? $data0 : $data1;
    }

    protected function _getNameByTable(string $tableName, ?string $connectionName, bool $force): mixed
    {
        $data = $force ? [] : $this->_getCacheData('reverce_link', []);

        // Try to pull entity name from the cache
        if (!empty($connectionName) && isset($data[$connectionName][$tableName])) {
            return $data[$connectionName][$tableName];
        }
        if (empty($connectionName) && !empty($data)) {
            foreach ($data as $v) {
                if (isset($v[$tableName])) {
                    return $v[$tableName];
                }
            }
        }

        // Make New data by FileSystem
        $ns   = rtrim($this->getNsPrefix(), '\\');
        $dirs = [
            $ns => \bootstrap::getLoader()->getPathByNS($ns),
        ];

        while (!empty($dirs)) {
            reset($dirs);
            $ns   = key($dirs);
            $dir  = array_shift($dirs);
            $list = scandir($dir);
            foreach ($list as $v) {
                $check = $dir . '/' . $v;
                if ($v !== '.' && $v !== '..' && is_dir($check)) {
                    if (file_exists($check . '/entity.php')) {
                        try {
                            $ett = $this->get($ns . '\\' . $v);
                        } catch (fatalException $e) {
                            continue;
                        }
                        $data[$ett->getConnectionName()][$ett->getTableName()] = $ett->getName();
                    } else {
                        $dirs[$ns . '\\' . $v] = $check;
                    }
                }
            }
        }

        // Save new data to the cache and return requested value
        $this->_setCacheData('reverce_link', $data);
        return array_val($data, [$connectionName, $tableName]);
    }


    protected function _getDelegate(mixed $name): mixed
    {
        return $this->get($name);
    }
}
