<?php

declare(strict_types=1);

namespace fan\core\service\entity;
use fan\project\exception\model\entity\fatal as fatalException;
/**
 * Entity table-description
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
 *
 * @property-read string|array $primeryKey
 * @method \fan\core\service\entity\description setPrimeryKey() setPrimeryKey(array|string $key)
 * @method mixed getPrimeryKey()
 * @property-read array $fields
 * @method \fan\core\service\entity\description setFields() setFields(array $fields)
 * @method mixed getFields()
 * @property-read array $keys
 * @method \fan\core\service\entity\description setKeys() setKeys(array $keys)
 * @method mixed getKeys()
 * @property-read array $relations
 * @method mixed getRelations()
 * @property-read array $dependents
 * @method \fan\core\service\entity\description setDependents()  setDependents(array $dependents)
 * @method mixed getDependents()
 * @property-read string $engine
 * @method string getEngine()
 * @property-read string $createTime
 * @method string getCreateTime()
 * @property-read string $tableCollation
 * @method string getTableCollation()
 * @property string $comment
 * @method string getComment()
 */
class description
{
    /**
     * Entity - owner of table description
     * @var \fan\core\base\model\entity
     */
    protected ?object $entity = null;
    /**
     * Table descriptor (Maker dynamic description)
     * @var \fan\core\service\entity\descriptor
     */
    protected ?object $descriptor = null;

    /**
     * List of applied property avaylable by magic functions
     * @var array
     */
    protected array $property = [
        'primeryKey'     => null,
        'fields'         => null,
        'keys'           => null,
        'relations'      => null,
        //'dependents'     => null,
        'engine'         => null,
        'createTime'     => null,
        'tableCollation' => null,
        'comment'        => null,
    ];

    /**
     * Enable save/load cache of entity structure
     * @var boolean
     */
    protected bool $cacheEnabled = true;
    /**
     * List of Property which set dynamically
     * @var array
     */
    protected array $dynamicProperty = [];

    public function __construct(\fan\core\base\model\entity $entity, array $param)
    {
        $this->entity = $entity;

        $this->cacheEnabled    = isset($param['cacheEnabled']) ? !empty($param['cacheEnabled']) : (bool)$entity->getConfig()->get('cacheEnabled', true);
        $this->dynamicProperty = $this->_defineDynamicProperty($param);
    }
    // ======== Static methods ======== \\
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
        $ret = $this->get((string)$key);
        return is_string($ret) ? (string)$ret : $ret; //ToDo: Examine this hack
    }

    public function __call(string $method, array $args): mixed
    {
        $method = (string)$method;
        $key = lcfirst(substr($method, 3));
        if (substr($method, 0, 3) === 'set' && $this->_checkPropertyName($key, false)) {
            $this->set($key, $args[0]);
            return $this;
        } elseif (substr($method, 0, 3) === 'get' && $this->_checkPropertyName($key, false)) {
            return $this->get($key, isset($args[0]) ? $args[0] : false);
        }
        throw new fatalException($this->getEntity(), 'Incorrect call of entity description!');
    }

    // ======== Required Interface methods ======== \\
    // ======== Main Interface methods ======== \\
    public function get(string $key, bool $force = false): mixed
    {
        if ($this->_checkPropertyName($key)) {
            if (is_null($this->property[$key])) {
                $this->_loadDynamicProperty($key, $force);
            }
            return $this->property[$key];
        }
        return null;
    }

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    public function set(string $key, mixed $value): static
    {
        if ($this->_checkPropertyName($key) && ($key === 'comment' || is_null($this->property[$key]))) {
            $method = 'set' . ucfirst($key);
            if (method_exists($this, $method)) {
                if ($method === 'setComment') {
                    $value = (string)$value;
                }
                $this->$method($value);
            } else {
                $this->property[$key] = $value;
            }
        }
        return $this;
    }

    /**
     * @param string $value Value that should be applied or transformed.
     */
    public function setComment(string $value): static
    {
        $this->getEntity()->getConnection()->execute('ALTER TABLE `' . $this->getTableName() . '` COMMENT = ?', [$value]);
        if ($this->cacheEnabled) {
            $this->_loadDynamicProperty();
        }
        $this->property['comment'] = (string)$value;
        $this->_saveCacheFile();
        return $this;
    }

    public function isTableExists(): bool
    {
        return $this->_getDescriptor()->isTableExists();
    }

    public function getTableName(): string
    {
        return $this->getEntity()->getTableName();
    }

// ToDo: Set dependents by entity-class/method (OR it will by recognized by DB-field automatically)

    public function toArray(): array
    {
        return $this->property;
    }

    public function getEntity(): \fan\core\base\model\entity
    {
        return $this->entity;
    }
    // ======== Private/Protected methods ======== \\

    /**
     * @throws fatalException
     */
    protected function _checkPropertyName(string $propName, bool $allowException = true): bool
    {
        if (array_key_exists($propName, $this->property)) {
            return true;
        }
        if ($allowException) {
            throw new fatalException($this->getEntity(), 'Incorret Property Name of Entity-desckription "' . $propName . '".');
        }
        return false;
    }

    protected function _getDescriptor(): \fan\core\service\entity\descriptor
    {
        if (is_null($this->descriptor)) {
            // ToDo: Define descriptor by type of current connection
            try {
                $this->descriptor = new \fan\project\service\entity\descriptor\mysql\schema($this);
            } catch (\fan\core\exception\model\reverse $e) {
                $this->descriptor = new \fan\project\service\entity\descriptor\mysql\direct($this);
            }
        }
        return $this->descriptor;
    }

    protected function _defineDynamicProperty(array $param = []): array
    {
        $result = [];
        foreach ($this->property as $k => $v) {
            if (isset($param[$k])) {
                $this->property[$k] = $param[$k]; // ToDo: Validate $param[$k] before set it
            } elseif (is_null($v)) {
                $result[] = $k;
            }
        }
        return $result;
    }

    protected function _loadDynamicProperty(?string $key = null, bool $force = false): static
    {
        $cacheFile = $this->_getCacheFileName();
        if ($this->cacheEnabled && file_exists($cacheFile) && !$force) {
            $property = \fan\project\adapter\php_array_file::load($cacheFile, []);
            foreach ($this->dynamicProperty as $k => $v) {
                $this->property[$v] = isset($property[$v]) ? $property[$v] : null;
                unset($this->dynamicProperty[$k]);
            }
        } elseif ($this->cacheEnabled || empty($key)) {
            if (!$this->isTableExists()) {
                throw new fatalException($this->getEntity(), 'DB table "' . $this->getTableName() . '" doesn\'t exists.');
            }
            $descriptor = $this->_getDescriptor();
            foreach ($this->property as $k => $v) {
                $method = 'get' . ucfirst($k);
                $this->property[$k] = $descriptor->$method();
            }
            $this->_saveCacheFile();
        } else {
            $method = 'get' . ucfirst($key);
            $this->property[$key] = $this->_getDescriptor()->$method();
        }
        return $this;
    }

    protected function _saveCacheFile(): static
    {
        if ($this->cacheEnabled) {
            $savedData = array_merge(
                    ['class' => get_class_alt($this->getEntity())],
                    $this->property
            );
            $cacheFile = $this->_getCacheFileName();
            file_put_contents($cacheFile, '<?php
/*
 * Entity structure array
 */
return ' . var_export($savedData, true) . ';
?>');
        }
        return $this;
    }
    protected function _getCacheFileName(): string
    {
        $param    = $this->getEntity()->getConnection()->getConnectionParam();
        $cacheDir = rtrim((string)$this->getEntity()->getService()->getConfig('CACHE_DIR', '{TEMP}/cache/entity'), '\\/') . '/';
        $cacheDir = \bootstrap::parsePath($cacheDir);
        return $cacheDir . $this->getTableName() . '_' . md5($param['HOST'] . $param['DATABASE']);
    }
}
