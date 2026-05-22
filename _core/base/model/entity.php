<?php

declare(strict_types=1);

namespace fan\core\base\model;
use fan\project\exception\model\entity\fatal as fatalException;
/**
 * Entity - table data
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
 * @property-read \fan\core\service\entity\description $description
 * @property-read \fan\core\base\model\request $request
 * @author: Alexandr Nosov (alex@4n.com.ua)
 * @version of file: 05.02.006 (20.04.2015)
 */
abstract class entity
{
    use \fan\core\di\container_aware_trait;

    /**
     * Entity Name (suffix of NS with class-name)
     *
     * @var string
     */
    protected ?string $name = null;
    /**
     * Table Name
     * @var string
     */
    protected ?string $tableName = null;

    /**
     * Service of entity
     * @var \fan\core\service\config\row
     */
    protected ?object $config = null;
    /**
     * Service of entity
     * @var \fan\core\service\entity
     */
    protected ?object $service = null;
    /**
     * Service of entity
     * @var \fan\core\service\database
     */
    protected ?object $connection = null;
    /**
     * Connection Name for \fan\core\service\database
     * @var string
     */
    protected ?string $connectionName = null;
    /**
     * Connection Key for \fan\core\service\database
     * @var string
     */
    protected string|int|float|null $connectionKey = null;

    protected array $sql = [];

    /**
     * Description of table of current Entity
     * @var \fan\core\service\entity\description
     */
    protected ?object $description = null;
    /**
     * Loader of SQL-request
     * @var \fan\core\base\model\request
     */
    protected ?object $request = null;

    /**
     * Backup of Call-Parameters
     * @var array
     */
    protected array $bakParam = [];

    /**
     * Name of Class for Row-object
     * @var string
     */
    protected ?string $rowClassName = null;
    /**
     * Name of Class for Rowset-object
     * @var string
     */
    protected ?string $rowsetClassName = null;
    /**
     * Name of Class for Request-object
     * @var string
     */
    protected ?string $requestClassName = null;

    public function __construct(\fan\core\service\entity $service, mixed $name, mixed $param = [])
    {
        $param = (array)$param;
        $this->service  = $service;
        $this->name     = is_null($name) ? null : (string)$name;

        $this->bakParam = $param;
        $this->config   = $this->containerService('config', 'entity')->getEntityConfig($this, $name);

        $this->_setConnectionParam($param);

        $this->_init($param);

        if (empty($this->tableName)) {
            $this->tableName = $this->_defineTableName($param);
        }

    }

    // ======== The magic methods ======== \\

    /**
     * Handles dynamic property writes for this current component.
     *
     * @param mixed $value Value that should be applied or transformed.
     *
     * @throws fatalException
     */
    public function __set(string $key, mixed $value): void
    {
        throw new fatalException($this, 'There is impossible to set property "' . $key . '".');
    }

    /**
     * Handles dynamic property reads for this current component.
     *
     * @throws fatalException
     */
    public function __get(string $key): mixed
    {
        $prop = $this->_getPropertyList();
        if (!isset($prop[$key])) {
            throw new fatalException($this, 'There is impossible to get property "' . $key . '".');
        }
        return $this->{$prop[$key]}();
    }

    // ======== Main Interface methods ======== \\
    // --===-- Get Row --===-- \\
    public function getNewRow(): \fan\core\base\model\row
    {
        return $this->_getRowByData();
    }

    public function getRowById(mixed $rowId, bool $idIsEncrypt = false): \fan\core\base\model\row
    {
        if (is_null($rowId)) {
            return $this->_getRowByData();
        }
        $param = $this->getParamById($rowId, $idIsEncrypt);
        return $this->getRowByParam($param, 0, null);
    }

    public function getRowByParam(mixed $param = null, int|float $offset = 0, ?string $orderBy = null): \fan\core\base\model\row
    {
        $data =& $this->getDataByParam($param, 1, $offset, $orderBy, true);
        return $this->_getRowByData($data);
    }

    public function getRowOrCreate(?array $loadParam = null, array $saveParam = [], bool $saveNew = true): \fan\core\base\model\row
    {
        $row = $this->getRowByParam($loadParam);
        if (!$row->checkIsLoad()) {
            $row->setFields(array_merge((array)$loadParam, $saveParam), $saveNew);
        }
        return $row;
    }

    public function getRowByKey(string $queryKey, mixed $param = null, int|float $offset = 0, ?string $orderBy = null): \fan\core\base\model\row
    {
        $designer = $this->getSnippetyDesigner($queryKey)->setOrderPart($orderBy);
        return $this->getRowByQuery($designer, $param, $offset);
    }

    public function getRowByQuery(string|\fan\core\service\entity\designer $query, mixed $param = null, int|float $offset = 0): \fan\core\base\model\row
    {
        $data  =& $this->getDataByQuery($query, $param, 1, $offset, true);
        return $this->_getRowByData($data);
    }

    // --===-- Get Rowset --===-- \\
    public function getRowsetByParam(mixed $param = null, int|float $qtt = -1, int|float $offset = -1, string $orderBy = ''): \fan\core\base\model\rowset
    {
        $class =  $this->getRowsetClassName();
        $data  =& $this->getDataByParam($param, $qtt, $offset, $orderBy);
        return new $class($this, $data);
    }

    public function getRowsetByKey(string $queryKey, mixed $param = null, int|float $qtt = -1, int|float $offset = -1, string $orderBy = ''): \fan\core\base\model\rowset
    {
        $designer = $this->getSnippetyDesigner($queryKey)->setOrderPart($orderBy);
        return $this->getRowsetByQuery($designer, $param, $qtt, $offset);
    }

    public function getRowsetByQuery(string|\fan\core\service\entity\designer $query, mixed $param = null, int|float $qtt = -1, int|float $offset = -1): \fan\core\base\model\rowset
    {
        $class =  $this->getRowsetClassName();
        $data  =& $this->getDataByQuery($query, $param, $qtt, $offset);
        return new $class($this, $data);
    }

    // --===-- Get Count --===-- \\
    public function getCountByParam(mixed $param = null): mixed
    {
        $query = $this->getDesigner('select')->setSelectByParam($param);
        return $this->getCountByQuery($query, $param);
    }

    public function getCountByKey(string $queryKey, mixed $param = null): mixed
    {
        $query = $this->getSnippetyDesigner($queryKey);
        return $this->getCountByQuery($query, $param);
    }

    public function getCountByQuery(string|\fan\core\service\entity\designer $query, mixed $param = null): mixed
    {
        list($query, $newParam) = $this->_getSqlAsString($query, $param);
        // ToDo: Take account of Union
        $matches = [];
        if (preg_match_all('/\s+ORDER\s+BY\s+[^)]*$/', $query, $matches)) {
            $query = str_replace(end($matches[0]), '', $query);
        }

        $method = $this->config['COUNT_METHOD'];
        if (empty($method)) {
            /* @var $globalConf \fan\core\service\config\row */
            $globalConf = $this->containerService('config', 'entity')->get('common');
            $method = $globalConf->get('DEFAULT_COUNT_METHOD', 'SUBQUERY');
        }

        $servDb = $this->getConnection();
        switch (strtoupper((string)$method)) {
        case 'CALC_FOUND_ROWS':
            $queryTmp = preg_replace('/(?<=^|\W)SELECT\s/i', 'SELECT SQL_CALC_FOUND_ROWS ', $query, 1);
            $servDb->getAllLimit($queryTmp, $newParam, 1);
            $query = 'SELECT FOUND_ROWS() as cnt';
            return $servDb->getOne($query, 'cnt');
        }
        $query = 'SELECT count(*) as cnt FROM (' . $query . ') as src';
        return $servDb->getOne($query, 'cnt', $newParam);
    }

    public function getTableName(): ?string
    {
        return $this->tableName;
    }
    // ---- Additional interface methods ---- \\
    /**
     * @throws fatalException
     */
    public function getParamById(mixed $rowId, bool $idIsEncrypt = false): array
    {
        $idName = $this->description->getPrimeryKey();
        if (is_scalar($idName)) {
            if (is_scalar($rowId)) {
                $param[$idName] = $idIsEncrypt ? $this->getService()->getEncapsulant()->decryptId((string)$rowId) : $rowId;
            } elseif (is_object($rowId) && method_exists($rowId, '__toString')) {
                $param[$idName] = $rowId->__toString();
            } else {
                throw new fatalException($this, 'Value of ID for select data from "' . $this->getTableName() . '" must have scalar value.');
            }
        } elseif (is_array($rowId) && count($idName) === count($rowId)) {
            sort($idName);
            ksort($rowId);
            if (array_diff($idName, array_keys($rowId))) {
                foreach (array_values($rowId) as $k => $v) {
                    $param[$idName[$k]] = $rowId;
                }
            } else {
                $param = $rowId;
            }
        } else {
            throw new fatalException($this, 'Value of ID for select data from "' . $this->getTableName() . '" must be as array.');
        }
        return $param;
    }

    public function &getDataByParam(mixed $param = null, int|float $qtt = -1, int|float $offset = -1, ?string $orderBy = null, bool $onlyOne = false): array
    {
        $query =  $this->getDesigner('select')->setSelectByParam($param, $orderBy);
        $data  =& $this->getDataByQuery($query, $param, $qtt, $offset, $onlyOne);
        return $data;
    }

    /**
     * @throws fatalException
     */
    public function &getDataByQuery(string|\fan\core\service\entity\designer $query, mixed $param = null, int|float $qtt = -1, int|float $offset = -1, bool $onlyOne = false): array
    {
        list($query, $newParam) = $this->_getSqlAsString($query, $param, false);
        $data = $this->getConnection()->getAllLimit($query, $newParam, $qtt, $offset);
        // ToDo: link Result to array as the property of this object
        if (!empty($data) && $onlyOne) {
            $data =& $data[0];
        }
        return $data;
    }

    /**
     * @param string $value Value that should be applied or transformed.
     */
    public function setSQL(string $queryKey, string $value): static
    {
        $this->getRequestLoader()->set($queryKey, $value);
        return $this;
    }
    public function getSQL(string $queryKey): string
    {
        return $this->getRequestLoader()->get($queryKey);
    }
    public function getSnippetyDesigner(string $queryKey): \fan\core\service\entity\designer\snippety
    {
        $designer = $this->getDesigner('snippety');
        /* @var $designer \fan\core\service\entity\designer\snippety */
        $designer->setSqlRequest($queryKey);
        return $designer;
    }

    public function setConnection(mixed $connection = null, mixed $extraKey = 0): static
    {
        if (empty($connection)) {
            $connection = $this->connectionName;
        }

        if (is_scalar($connection) || is_null($connection)) {
            if (empty($extraKey)) {
                $extraKey = $this->connectionKey;
            }
            $connection = $this->containerService('database', is_null($connection) ? null : (string)$connection, $extraKey);
        } elseif (is_object($connection) && $connection instanceof \fan\core\service\database) {
            $connection = $connection;
        } else {
            throw new fatalException($this, 'Incorrect connection.');
        }

        $this->connection  = $connection;
        $this->description = null;
        return $this;
    }
    public function getConnection(): \fan\core\service\database
    {
        if (!$this->connection) {
            $this->setConnection();
        }
        return $this->connection;
    }

    public function setConnectionName(string $connectionName): static
    {
        $this->connectionName = $connectionName;
        return $this;
    }
    public function getConnectionName(): ?string
    {
        return $this->connectionName;
    }

    public function setConnectionKey(mixed $connectionKey): static
    {
        $this->connectionKey = $connectionKey;
        return $this;
    }
    public function getConnectionKey(): string|int|float|null
    {
        return $this->connectionKey;
    }
    public function getMainParam(): array
    {
        return [
            'collection' => $this->getService()->getCollectionKey(),
            'name'       => $this->getName(),
            'class'      => get_class($this),
            'param'      => $this->bakParam,
            'connection' => [
                'name' => $this->getConnectionName(),
                'key'  => $this->getConnectionKey(),
            ],
        ];
    }

    public function getName(bool $showAlter = false): ?string
    {
        return empty($this->name) && $showAlter ? '(Anonymous)' . $this->getTableName() : $this->name;
    }

    public function getService(): \fan\core\service\entity
    {
        return $this->service;
    }
    /**
     * @param mixed $default Fallback value returned when no explicit value is available.
     */
    public function getConfig(mixed $key = null, mixed $default = null): mixed
    {
        return is_null($key) ? $this->config : $this->config->get($key, $default);
    }

    public function getDesigner(string $type = 'select'): \fan\core\service\entity\designer
    {
        return $this->getService()->getDesigner($this, $type);
    }

    public function getDescription(array $param = []): \fan\core\service\entity\description
    {
        if (is_null($this->description)) {
            $this->description = $this->getService()->getDescription($this, array_merge((array)$param, $this->bakParam));
        }
        return $this->description;
    }
    public function getRequestLoader(array $sql = []): \fan\core\base\model\request
    {
        if (is_null($this->request)) {
            $className = $this->getRequestClassName();
            $this->request = new $className($this);
        }
        if (!empty($sql)) {
            $this->request->setRequests($sql);
        }
        return $this->request;
    }

    public function getRowClassName(): string
    {
        if (empty($this->rowClassName)) {
            $this->rowClassName = $this->_getClassName('row');
        }
        return $this->rowClassName;
    }
    public function getRowsetClassName(): string
    {
        if (empty($this->rowsetClassName)) {
            $this->rowsetClassName = $this->_getClassName('rowset');
        }
        return $this->rowsetClassName;
    }
    public function getRequestClassName(): string
    {
        if (empty($this->requestClassName)) {
            $this->requestClassName = $this->_getClassName('request');
        }
        return $this->requestClassName;
    }

    public function getTableStatus(): mixed
    {
        return $this->getConnection()->getTableStatus((string)$this->tableName);
    }

    public function getCheckKey(int $reduce = 0): string
    {
        $tmp = $this->getTableStatus();
        $key = md5(
            ($tmp['Rows'] ?? '') .
            ($tmp['Avg_row_length'] ?? '') .
            ($tmp['Data_length'] ?? '') .
            ($tmp['Index_length'] ?? '') .
            ($tmp['Auto_increment'] ?? '') .
            ($tmp['Update_time'] ?? '') .
            ($tmp['Checksum'] ?? '')
        );
        if ($reduce > 0) {
            return substr($key, 0, $reduce);
        } elseif ($reduce < 0) {
            return substr($key, $reduce);
        } else {
            return $key;
        }
    }

    // ======== Private/Protected methods ======== \\
    protected function _init(array $param): static
    {
        return $this;
    }

    /**
     * @throws fatalException
     */
    protected function _defineTableName(array $param = []): string
    {
        if (isset($param['tableName'])) {
            return $param['tableName'];
        }
        $name = $this->getName();
        $matches = [];
        if (preg_match('/^(?:.+\\\\)?(\w+)$/', (string)$name, $matches)) {
            return $matches[1];
        }
        throw new fatalException($this, 'Can\'t define the Table name for "' . get_class($this) . '".');
    }

    protected function _getPropertyList(): array
    {
        return [
            'description' => 'getDescription',
            'request'     => 'getRequestLoader',
        ];
    }
    protected function _setConnectionParam(array $param): static
    {
        if (isset($param['connectionName'])) {
            $this->connectionName = (string)$param['connectionName'];
        } else {
            $connectionName = $this->config['CONNECTION'];
            while (empty($connectionName)) {
                $globalConf = $this->containerService('config', 'entity')->get('common');
                if (isset($globalConf['CONNECTIONS'])) {
                    $prefix = trim($this->getService()->getNsPrefix(), '\\');
                    $len    = strlen($prefix);
                    $ns     = get_ns_name($this, 2);
                    for ($i = 0; $i < 2; $i++) {
                        if (isset($globalConf['CONNECTIONS'][$ns])) {
                            $connectionName = $globalConf['CONNECTIONS'][$ns];
                            break 2;
                        }
                        if (strncmp($ns, $prefix, $len) !== 0) {
                            break;
                        }
                        $ns = trim(substr($ns, $len), '\\');
                        if (empty($ns)) {
                            break;
                        }
                    }
                }
                $connectionName = $globalConf['DEFAULT_CONNECTION'];
                break;
            }
            $this->connectionName = (string)$connectionName;
        }
        $this->connectionKey = isset($param['connectionKey']) ? (int)$param['connectionKey'] : 0;
        return $this;
    }

    /**
     * @throws fatalException
     */
    protected function _getClassName(string $key): string
    {
        $name = $this->getName();
        if (empty($name)) {
            $className = '';
        } else {
            $prefix = $this->getService()->getNsPrefix();
            if (empty($prefix)) {
                throw new fatalException($this, 'In config prefix doesn\'t set for "' . $key . '".');
            }

            $className = $prefix . $name . '\\' . $key;
        }
        if (empty($className) || !class_exists($className)) {
            $className = '\fan\project\base\model\\' . $key;
        }

        $reflection = new \ReflectionClass($className);
        do {
            if ($reflection->getName() === 'fan\core\base\model\\' . $key) {
                return $className;
            }
            $reflection = $reflection->getParentClass();
        } while (!empty($reflection));

        throw new fatalException($this, 'Class "' . $className . '" must be instance of "\fan\core\base\model\\' . $key . '".');
    }

    /**
     * @throws fatalException
     */
    protected function _getSqlAsString(string|\fan\core\service\entity\designer $query, mixed $param): array
    {
        if (is_object($query) && $query instanceof \fan\core\service\entity\designer) {
            return [$query->assemble($param), $query->getAdjustedParam()];
        } elseif (!is_string($query)) {
            return [$query, $param];
        }
        throw new fatalException($this, 'Incorrect format of SQL-request.');
    }

    protected function _getRowByData(?array &$data = null): \fan\core\base\model\row
    {
        $class = $this->getRowClassName();
        return empty($data) ? new $class($this) : new $class($this, $data);
    }
}
