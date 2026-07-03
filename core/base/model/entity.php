<?php

declare(strict_types=1);

namespace fan\core\base\model;
use fan\core\base\model\request;
use fan\core\base\model\row;
use fan\core\base\model\rowset;

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
 * @property-read object $description
 * @property-read \fan\core\base\model\request $request
 * @author: Alexandr Nosov (alex@4n.com.ua)
 * @version of file: 05.02.006 (20.04.2015)
 */
abstract class entity
{
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
     * @var object
     */
    protected ?object $connection = null;
    /**
     * Connection Name for database factory
     * @var string
     */
    protected ?string $connectionName = null;
    /**
     * Connection Key for database factory
     * @var string
     */
    protected string|int|float|null $connectionKey = null;

    protected array $sql = [];

    /**
     * Description of table of current Entity
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

    private mixed $configFactory = null;

    private mixed $databaseFactory = null;

    private mixed $reflectorFactory = null;

    private mixed $rowFactory = null;

    private mixed $rowsetFactory = null;

    private mixed $requestLoaderFactory = null;

    private mixed $modelEntityExceptionFactory = null;

    private ?\Closure $namespaceResolver = null;

    private ?object $reflectionClassFactory = null;

    private mixed $entityIdDecoder = null;

    private mixed $entityLookup = null;

    private mixed $designerFactory = null;

    private mixed $descriptionProvider = null;

    private mixed $namespacePrefixResolver = null;

    private mixed $collectionKeyProvider = null;

    private mixed $sqlDirectoryProvider = null;

    private mixed $rowDependenciesProvider = null;

    private mixed $fileDataRowDependenciesProvider = null;

    private mixed $specFileImageRowDependenciesProvider = null;

    private mixed $relatedEntityRowFactory = null;

    private \Closure $modelClassExists;

    public function __construct(
        object $service,
        mixed $name,
        mixed $param = [],
        ?callable $configFactory = null,
        ?callable $databaseFactory = null,
        ?callable $reflectorFactory = null,
        ?callable $rowFactory = null,
        ?callable $rowsetFactory = null,
        ?callable $requestLoaderFactory = null,
        ?callable $modelEntityExceptionFactory = null,
        callable|entity_dependencies|null $namespaceResolver = null,
        ?object $reflectionClassFactory = null,
        ?callable $entityIdDecoder = null,
        ?callable $entityLookup = null,
        ?callable $designerFactory = null,
        ?callable $descriptionProvider = null,
        ?callable $namespacePrefixResolver = null,
        ?callable $collectionKeyProvider = null,
        ?callable $sqlDirectoryProvider = null,
        ?callable $rowDependenciesProvider = null,
        ?callable $fileDataRowDependenciesProvider = null,
        ?callable $specFileImageRowDependenciesProvider = null,
        ?callable $relatedEntityRowFactory = null,
        ?callable $modelClassExists = null
    )
    {
        $param = (array)$param;
        $this->service  = $service;
        $this->name     = is_null($name) ? null : (string)$name;
        $this->namespaceResolver = $this->defaultNamespaceResolver();
        if ($namespaceResolver instanceof entity_dependencies) {
            $entityDependencies = $namespaceResolver;
            $namespaceResolver = $entityDependencies->namespaceResolver;
            $reflectionClassFactory ??= $entityDependencies->reflectionClassFactory;
            $entityIdDecoder ??= $entityDependencies->entityIdDecoder;
            $entityLookup ??= $entityDependencies->entityLookup;
            $designerFactory ??= $entityDependencies->designerFactory;
            $descriptionProvider ??= $entityDependencies->descriptionProvider;
            $namespacePrefixResolver ??= $entityDependencies->namespacePrefixResolver;
            $collectionKeyProvider ??= $entityDependencies->collectionKeyProvider;
            $sqlDirectoryProvider ??= $entityDependencies->sqlDirectoryProvider;
            $rowDependenciesProvider ??= $entityDependencies->rowDependenciesProvider;
            $fileDataRowDependenciesProvider ??= $entityDependencies->fileDataRowDependenciesProvider;
            $specFileImageRowDependenciesProvider ??= $entityDependencies->specFileImageRowDependenciesProvider;
            $relatedEntityRowFactory ??= $entityDependencies->relatedEntityRowFactory;
            $modelClassExists ??= $entityDependencies->modelClassExists;
        }
        $entityIdDecoder ??= static fn(string $rowId): mixed => $service->getEncapsulant()->decryptId($rowId);
        $entityLookup ??= static fn(string $tableName, ?string $connectionName = null): mixed => $service->getEntityByTable($tableName, $connectionName);
        $designerFactory ??= static fn(entity $entity, string $type = 'select'): object => $service->getDesigner($entity, $type);
        $descriptionProvider ??= static fn(entity $entity, array $param = []): object => $service->getDescription($entity, $param);
        $namespacePrefixResolver ??= static fn(entity $entity): string => $service->getNsPrefix();
        $collectionKeyProvider ??= static fn(entity $entity): mixed => $service->getCollectionKey();
        $sqlDirectoryProvider ??= static fn(entity $entity): string => $service->getSqlDir();
        $rowDependenciesProvider ??= static fn(entity $entity): array => method_exists($service, 'getRowDependencies') ? $service->getRowDependencies() : [];
        $fileDataRowDependenciesProvider ??= static fn(entity $entity): array => method_exists($service, 'getFileDataRowDependencies') ? $service->getFileDataRowDependencies() : [];
        $specFileImageRowDependenciesProvider ??= static fn(entity $entity): array => method_exists($service, 'getSpecFileImageRowDependencies') ? $service->getSpecFileImageRowDependencies() : [];
        $relatedEntityRowFactory ??= static fn(entity $entity, string $entityName): object => $service->get($entityName)->getNewRow();
        $this->setEntityDependencies(
            $configFactory,
            $databaseFactory,
            $reflectorFactory,
            $rowFactory,
            $rowsetFactory,
            $requestLoaderFactory,
            $modelEntityExceptionFactory,
            entityDependencies: new entity_dependencies(
                $namespaceResolver,
                $reflectionClassFactory,
                $entityIdDecoder,
                $entityLookup,
                $designerFactory,
                $descriptionProvider,
                $namespacePrefixResolver,
                $collectionKeyProvider,
                $sqlDirectoryProvider,
                $rowDependenciesProvider,
                $fileDataRowDependenciesProvider,
                $specFileImageRowDependenciesProvider,
                $relatedEntityRowFactory,
                $modelClassExists
            )
        );

        $this->bakParam = $param;
        $this->config   = $this->configService()->getEntityConfig($this, $name);

        $this->_setConnectionParam($param);

        $this->_init($param);

        if (empty($this->tableName)) {
            $this->tableName = $this->_defineTableName($param);
        }

    }

    public function setEntityDependencies(
        ?callable $configFactory = null,
        ?callable $databaseFactory = null,
        ?callable $reflectorFactory = null,
        ?callable $rowFactory = null,
        ?callable $rowsetFactory = null,
        ?callable $requestLoaderFactory = null,
        ?callable $modelEntityExceptionFactory = null,
        callable|entity_dependencies|null $namespaceResolver = null,
        ?object $reflectionClassFactory = null,
        ?callable $entityIdDecoder = null,
        ?callable $entityLookup = null,
        ?callable $designerFactory = null,
        ?callable $descriptionProvider = null,
        ?callable $namespacePrefixResolver = null,
        ?callable $collectionKeyProvider = null,
        ?callable $sqlDirectoryProvider = null,
        ?callable $rowDependenciesProvider = null,
        ?callable $fileDataRowDependenciesProvider = null,
        ?callable $specFileImageRowDependenciesProvider = null,
        ?callable $relatedEntityRowFactory = null,
        ?callable $modelClassExists = null,
        ?entity_dependencies $entityDependencies = null
    ): static
    {
        if ($namespaceResolver instanceof entity_dependencies) {
            $entityDependencies = $namespaceResolver;
            $namespaceResolver = null;
        }
        if ($entityDependencies !== null) {
            $this->applyEntityDependencies($entityDependencies);
        }
        if ($configFactory !== null) {
            $this->configFactory = $configFactory;
        }
        if ($databaseFactory !== null) {
            $this->databaseFactory = $databaseFactory;
        }
        if ($reflectorFactory !== null) {
            $this->reflectorFactory = $reflectorFactory;
        }
        if ($rowFactory !== null) {
            $this->rowFactory = $rowFactory;
        }
        if ($rowsetFactory !== null) {
            $this->rowsetFactory = $rowsetFactory;
        }
        if ($requestLoaderFactory !== null) {
            $this->requestLoaderFactory = $requestLoaderFactory;
        }
        if ($modelEntityExceptionFactory !== null) {
            $this->modelEntityExceptionFactory = $modelEntityExceptionFactory;
        }
        if ($namespaceResolver !== null) {
            $this->namespaceResolver = \Closure::fromCallable($namespaceResolver);
        }
        if ($reflectionClassFactory !== null) {
            $this->reflectionClassFactory = $reflectionClassFactory;
        }
        if ($entityIdDecoder !== null) {
            $this->entityIdDecoder = \Closure::fromCallable($entityIdDecoder);
        }
        if ($entityLookup !== null) {
            $this->entityLookup = \Closure::fromCallable($entityLookup);
        }
        if ($designerFactory !== null) {
            $this->designerFactory = \Closure::fromCallable($designerFactory);
        }
        if ($descriptionProvider !== null) {
            $this->descriptionProvider = \Closure::fromCallable($descriptionProvider);
        }
        if ($namespacePrefixResolver !== null) {
            $this->namespacePrefixResolver = \Closure::fromCallable($namespacePrefixResolver);
        }
        if ($collectionKeyProvider !== null) {
            $this->collectionKeyProvider = \Closure::fromCallable($collectionKeyProvider);
        }
        if ($sqlDirectoryProvider !== null) {
            $this->sqlDirectoryProvider = \Closure::fromCallable($sqlDirectoryProvider);
        }
        if ($rowDependenciesProvider !== null) {
            $this->rowDependenciesProvider = \Closure::fromCallable($rowDependenciesProvider);
        }
        if ($fileDataRowDependenciesProvider !== null) {
            $this->fileDataRowDependenciesProvider = \Closure::fromCallable($fileDataRowDependenciesProvider);
        }
        if ($specFileImageRowDependenciesProvider !== null) {
            $this->specFileImageRowDependenciesProvider = \Closure::fromCallable($specFileImageRowDependenciesProvider);
        }
        if ($relatedEntityRowFactory !== null) {
            $this->relatedEntityRowFactory = \Closure::fromCallable($relatedEntityRowFactory);
        }
        if ($modelClassExists !== null) {
            $this->modelClassExists = \Closure::fromCallable($modelClassExists);
        }
        if (!isset($this->modelClassExists)) {
            $this->modelClassExists = (new entity_dependencies())->modelClassExists;
        }

        return $this;
    }

    private function applyEntityDependencies(entity_dependencies $dependencies): void
    {
        if ($dependencies->namespaceResolver !== null) {
            $this->namespaceResolver = $dependencies->namespaceResolver;
        }
        if ($dependencies->reflectionClassFactory !== null) {
            $this->reflectionClassFactory = $dependencies->reflectionClassFactory;
        }
        if ($dependencies->entityIdDecoder !== null) {
            $this->entityIdDecoder = $dependencies->entityIdDecoder;
        }
        if ($dependencies->entityLookup !== null) {
            $this->entityLookup = $dependencies->entityLookup;
        }
        if ($dependencies->designerFactory !== null) {
            $this->designerFactory = $dependencies->designerFactory;
        }
        if ($dependencies->descriptionProvider !== null) {
            $this->descriptionProvider = $dependencies->descriptionProvider;
        }
        if ($dependencies->namespacePrefixResolver !== null) {
            $this->namespacePrefixResolver = $dependencies->namespacePrefixResolver;
        }
        if ($dependencies->collectionKeyProvider !== null) {
            $this->collectionKeyProvider = $dependencies->collectionKeyProvider;
        }
        if ($dependencies->sqlDirectoryProvider !== null) {
            $this->sqlDirectoryProvider = $dependencies->sqlDirectoryProvider;
        }
        if ($dependencies->rowDependenciesProvider !== null) {
            $this->rowDependenciesProvider = $dependencies->rowDependenciesProvider;
        }
        if ($dependencies->fileDataRowDependenciesProvider !== null) {
            $this->fileDataRowDependenciesProvider = $dependencies->fileDataRowDependenciesProvider;
        }
        if ($dependencies->specFileImageRowDependenciesProvider !== null) {
            $this->specFileImageRowDependenciesProvider = $dependencies->specFileImageRowDependenciesProvider;
        }
        if ($dependencies->relatedEntityRowFactory !== null) {
            $this->relatedEntityRowFactory = $dependencies->relatedEntityRowFactory;
        }
        if ($dependencies->modelClassExists !== null) {
            $this->modelClassExists = $dependencies->modelClassExists;
        }
    }

    private function createModelEntityFatalException(string $message, int $code = E_USER_ERROR, ?\Throwable $previous = null): \Throwable
    {
        if (!is_callable($this->modelEntityExceptionFactory)) {
            throw new \RuntimeException('Model entity exception factory is not configured for model entity.');
        }

        $exception = ($this->modelEntityExceptionFactory)(
            '\fan\project\exception\model\entity\fatal',
            $this,
            $message,
            $code,
            $previous
        );
        if (!$exception instanceof \Throwable) {
            $actual = is_object($exception) ? get_class($exception) : gettype($exception);
            throw new \UnexpectedValueException('Model entity exception factory returned "' . $actual . '".');
        }

        return $exception;
    }

    public function createDescriptionFatalException(string $message, int $code = E_USER_ERROR, ?\Throwable $previous = null): \Throwable
    {
        return $this->createModelEntityFatalException($message, $code, $previous);
    }

    public function createRowsetFatalException(string $message, int $code = E_USER_ERROR, ?\Throwable $previous = null): \Throwable
    {
        return $this->createModelEntityFatalException($message, $code, $previous);
    }

    public function createRequestFatalException(string $message, int $code = E_USER_ERROR, ?\Throwable $previous = null): \Throwable
    {
        return $this->createModelEntityFatalException($message, $code, $previous);
    }

    public function createDesignerFatalException(string $message, int $code = E_USER_ERROR, ?\Throwable $previous = null): \Throwable
    {
        return $this->createModelEntityFatalException($message, $code, $previous);
    }

    private function configService(): object
    {
        return $this->configFactory !== null ? ($this->configFactory)() : throw new \RuntimeException('Entity config service is not configured for model entity.');
    }

    private function databaseService(?string $connectionName = null, mixed $extraKey = 0): object
    {
        return $this->databaseFactory !== null ? ($this->databaseFactory)($connectionName, $extraKey) : throw new \RuntimeException('Database service is not configured for model entity.');
    }

    private function reflectorService(): object
    {
        return $this->reflectorFactory !== null ? ($this->reflectorFactory)() : throw new \RuntimeException('Reflector service is not configured for model entity.');
    }

    private function createRow(?array &$data = null, ?rowset $rowset = null): row
    {
        if (!is_callable($this->rowFactory)) {
            throw new \RuntimeException('Row factory is not configured for model entity.');
        }

        $className = $this->getRowClassName();
        if ($data === null) {
            $emptyData = [];
            $row = ($this->rowFactory)($className, $this, $emptyData, $rowset);
        } else {
            $row = ($this->rowFactory)($className, $this, $data, $rowset);
        }
        if (!$row instanceof row) {
            $actual = is_object($row) ? get_class($row) : gettype($row);
            throw new \UnexpectedValueException('Row factory returned "' . $actual . '".');
        }

        return $row;
    }

    private function createRowset(array &$data): rowset
    {
        if (!is_callable($this->rowsetFactory)) {
            throw new \RuntimeException('Rowset factory is not configured for model entity.');
        }
        if (!is_callable($this->rowFactory)) {
            throw new \RuntimeException('Row factory is not configured for model entity.');
        }

        $rowset = ($this->rowsetFactory)($this->getRowsetClassName(), $this, $data, $this->rowFactory);
        if (!$rowset instanceof rowset) {
            $actual = is_object($rowset) ? get_class($rowset) : gettype($rowset);
            throw new \UnexpectedValueException('Rowset factory returned "' . $actual . '".');
        }

        return $rowset;
    }

    private function createRequestLoader(string $className): request
    {
        if (!is_callable($this->requestLoaderFactory)) {
            throw new \RuntimeException('Request loader factory is not configured for model entity.');
        }

        $request = ($this->requestLoaderFactory)($className, $this, $this->reflectorService());
        if (!$request instanceof request) {
            $actual = is_object($request) ? get_class($request) : gettype($request);
            throw new \UnexpectedValueException('Request loader factory returned "' . $actual . '".');
        }

        return $request;
    }

    // ======== The magic methods ======== \\

    /**
     * Handles dynamic property writes for this current component.
     *
     * @param mixed $value Value that should be applied or transformed.
     *
     * @throws \fan\project\exception\model\entity\fatal
     */
    public function __set(string $key, mixed $value): void
    {
        throw $this->createModelEntityFatalException('There is impossible to set property "' . $key . '".');
    }

    /**
     * Handles dynamic property reads for this current component.
     *
     * @throws \fan\project\exception\model\entity\fatal
     */
    public function __get(string $key): mixed
    {
        $prop = $this->_getPropertyList();
        if (!isset($prop[$key])) {
            throw $this->createModelEntityFatalException('There is impossible to get property "' . $key . '".');
        }
        return $this->{$prop[$key]}();
    }

    // ======== Main Interface methods ======== \\
    // --===-- Get Row --===-- \\
    public function getNewRow(): row
    {
        return $this->_getRowByData();
    }

    public function getRowById(mixed $rowId, bool $idIsEncrypt = false): row
    {
        if (is_null($rowId)) {
            return $this->_getRowByData();
        }
        $param = $this->getParamById($rowId, $idIsEncrypt);
        return $this->getRowByParam($param, 0, null);
    }

    public function getRowByParam(mixed $param = null, int|float $offset = 0, ?string $orderBy = null): row
    {
        $data =& $this->getDataByParam($param, 1, $offset, $orderBy, true);
        return $this->_getRowByData($data);
    }

    public function getRowOrCreate(?array $loadParam = null, array $saveParam = [], bool $saveNew = true): row
    {
        $row = $this->getRowByParam($loadParam);
        if (!$row->checkIsLoad()) {
            $row->setFields(array_merge((array)$loadParam, $saveParam), $saveNew);
        }
        return $row;
    }

    public function getRowByKey(string $queryKey, mixed $param = null, int|float $offset = 0, ?string $orderBy = null): row
    {
        $designer = $this->getSnippetyDesigner($queryKey)->setOrderPart($orderBy);
        return $this->getRowByQuery($designer, $param, $offset);
    }

    public function getRowByQuery(string|object $query, mixed $param = null, int|float $offset = 0): row
    {
        $data  =& $this->getDataByQuery($query, $param, 1, $offset, true);
        return $this->_getRowByData($data);
    }

    // --===-- Get Rowset --===-- \\
    public function getRowsetByParam(mixed $param = null, int|float $qtt = -1, int|float $offset = -1, string $orderBy = ''): rowset
    {
        $data  =& $this->getDataByParam($param, $qtt, $offset, $orderBy);
        return $this->createRowset($data);
    }

    public function getRowsetByKey(string $queryKey, mixed $param = null, int|float $qtt = -1, int|float $offset = -1, string $orderBy = ''): rowset
    {
        $designer = $this->getSnippetyDesigner($queryKey)->setOrderPart($orderBy);
        return $this->getRowsetByQuery($designer, $param, $qtt, $offset);
    }

    public function getRowsetByQuery(string|object $query, mixed $param = null, int|float $qtt = -1, int|float $offset = -1): rowset
    {
        $data  =& $this->getDataByQuery($query, $param, $qtt, $offset);
        return $this->createRowset($data);
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

    public function getCountByQuery(string|object $query, mixed $param = null): mixed
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
            $globalConf = $this->configService()->get('common');
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
     * @throws \fan\project\exception\model\entity\fatal
     */
    public function getParamById(mixed $rowId, bool $idIsEncrypt = false): array
    {
        $param = [];
        $idName = $this->description->getPrimeryKey();
        if (is_scalar($idName)) {
            if (is_scalar($rowId)) {
                $param[$idName] = $idIsEncrypt ? $this->decodeEntityId((string)$rowId) : $rowId;
            } elseif (is_object($rowId) && method_exists($rowId, '__toString')) {
                $param[$idName] = $rowId->__toString();
            } else {
                throw $this->createModelEntityFatalException('Value of ID for select data from "' . $this->getTableName() . '" must have scalar value.');
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
            throw $this->createModelEntityFatalException('Value of ID for select data from "' . $this->getTableName() . '" must be as array.');
        }
        return $param;
    }

    public function decodeEntityId(string $rowId): mixed
    {
        if (!is_callable($this->entityIdDecoder)) {
            throw new \RuntimeException('Entity id decoder is not configured for model entity.');
        }

        return ($this->entityIdDecoder)($rowId);
    }

    public function findEntityByTable(string $tableName, ?string $connectionName = null): ?object
    {
        if (!is_callable($this->entityLookup)) {
            throw new \RuntimeException('Entity lookup is not configured for model entity.');
        }

        $entity = ($this->entityLookup)($tableName, $connectionName);

        return is_object($entity) ? $entity : null;
    }

    public function &getDataByParam(mixed $param = null, int|float $qtt = -1, int|float $offset = -1, ?string $orderBy = null, bool $onlyOne = false): array
    {
        $query =  $this->getDesigner('select')->setSelectByParam($param, $orderBy);
        $data  =& $this->getDataByQuery($query, $param, $qtt, $offset, $onlyOne);
        return $data;
    }

    /**
     * @throws \fan\project\exception\model\entity\fatal
     */
    public function &getDataByQuery(string|object $query, mixed $param = null, int|float $qtt = -1, int|float $offset = -1, bool $onlyOne = false): array
    {
        list($query, $newParam) = $this->_getSqlAsString($query, $param);
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
    public function getSnippetyDesigner(string $queryKey): object
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
            $connection = $this->databaseService(is_null($connection) ? null : (string)$connection, $extraKey);
        } elseif (is_object($connection)) {
            $connection = $connection;
        } else {
            throw $this->createModelEntityFatalException('Incorrect connection.');
        }

        $this->connection  = $connection;
        $this->description = null;
        return $this;
    }
    public function getConnection(): object
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
            'collection' => $this->entityCollectionKey(),
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

    public function getService(): object
    {
        return $this->service;
    }

    public function getSqlDirectory(): string
    {
        return $this->entitySqlDirectory();
    }

    public function rowDependencies(): array
    {
        return $this->entityDependencyList($this->rowDependenciesProvider, 'Entity row dependencies provider');
    }

    public function fileDataRowDependencies(): array
    {
        return $this->entityDependencyList($this->fileDataRowDependenciesProvider, 'Entity file-data row dependencies provider');
    }

    public function specFileImageRowDependencies(): array
    {
        return $this->entityDependencyList($this->specFileImageRowDependenciesProvider, 'Entity spec-file image row dependencies provider');
    }

    public function createRelatedEntityRow(string $entityName): object
    {
        if (!is_callable($this->relatedEntityRowFactory)) {
            throw new \RuntimeException('Related entity row factory is not configured for model entity.');
        }

        $row = ($this->relatedEntityRowFactory)($this, $entityName);
        if (!is_object($row)) {
            $actual = gettype($row);
            throw new \UnexpectedValueException('Related entity row factory returned "' . $actual . '".');
        }

        return $row;
    }

    /**
     * @param mixed $default Fallback value returned when no explicit value is available.
     */
    public function getConfig(mixed $key = null, mixed $default = null): mixed
    {
        return is_null($key) ? $this->config : $this->config->get($key, $default);
    }

    public function getDesigner(string $type = 'select'): object
    {
        return $this->createDesigner($type);
    }

    public function getDescription(array $param = []): object
    {
        if (is_null($this->description)) {
            $this->description = $this->loadDescription(array_merge((array)$param, $this->bakParam));
        }
        return $this->description;
    }
    public function getRequestLoader(array $sql = []): request
    {
        if (is_null($this->request)) {
            $className = $this->getRequestClassName();
            $this->request = $this->createRequestLoader($className);
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
     * @throws \fan\project\exception\model\entity\fatal
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
        throw $this->createModelEntityFatalException('Can\'t define the Table name for "' . get_class($this) . '".');
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
                $globalConf = $this->configService()->get('common');
                if (isset($globalConf['CONNECTIONS'])) {
                    $prefix = trim($this->entityNamespacePrefix(), '\\');
                    $len    = strlen($prefix);
                    $ns     = $this->namespaceName($this, 2);
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

    private function namespaceName(object|string $object, int $depth = 1): string
    {
        $namespace = ($this->namespaceResolver())($object, $depth);
        if (!is_string($namespace)) {
            throw new \UnexpectedValueException('Namespace resolver must return a string.');
        }

        return $namespace;
    }

    private static function nativeNamespaceName(object|string $object, int $depth = 1): string
    {
        if ($depth < 0 || $depth > 40) {
            return '';
        }

        $name = is_object($object) ? get_class($object) : $object;
        for ($i = 0; $i < $depth; $i++) {
            $position = strrpos($name, '\\');
            $name = $position > 0 ? substr($name, 0, $position) : '';
        }

        return $name;
    }

    private function reflectionClass(object|string $className): \ReflectionClass
    {
        if ($this->reflectionClassFactory === null || !method_exists($this->reflectionClassFactory, 'create')) {
            throw new \RuntimeException('Reflection class factory must expose create().');
        }

        $reflection = $this->reflectionClassFactory->create($className);
        if (!$reflection instanceof \ReflectionClass) {
            $actual = is_object($reflection) ? get_class($reflection) : gettype($reflection);
            throw new \UnexpectedValueException('Reflection class factory returned "' . $actual . '".');
        }

        return $reflection;
    }

    private function createDesigner(string $type): object
    {
        if (!is_callable($this->designerFactory)) {
            throw new \RuntimeException('Entity designer factory is not configured for model entity.');
        }

        $designer = ($this->designerFactory)($this, $type);
        if (!is_object($designer)) {
            $actual = gettype($designer);
            throw new \UnexpectedValueException('Entity designer factory returned "' . $actual . '".');
        }

        return $designer;
    }

    private function loadDescription(array $param): object
    {
        if (!is_callable($this->descriptionProvider)) {
            throw new \RuntimeException('Entity description provider is not configured for model entity.');
        }

        $description = ($this->descriptionProvider)($this, $param);
        if (!is_object($description)) {
            $actual = gettype($description);
            throw new \UnexpectedValueException('Entity description provider returned "' . $actual . '".');
        }

        return $description;
    }

    private function entityNamespacePrefix(): string
    {
        if (!is_callable($this->namespacePrefixResolver)) {
            throw new \RuntimeException('Entity namespace prefix resolver is not configured for model entity.');
        }

        $prefix = ($this->namespacePrefixResolver)($this);
        if (!is_string($prefix)) {
            $actual = is_object($prefix) ? get_class($prefix) : gettype($prefix);
            throw new \UnexpectedValueException('Entity namespace prefix resolver returned "' . $actual . '".');
        }

        return $prefix;
    }

    private function entityCollectionKey(): mixed
    {
        if (!is_callable($this->collectionKeyProvider)) {
            throw new \RuntimeException('Entity collection key provider is not configured for model entity.');
        }

        return ($this->collectionKeyProvider)($this);
    }

    private function entitySqlDirectory(): string
    {
        if (!is_callable($this->sqlDirectoryProvider)) {
            throw new \RuntimeException('Entity SQL directory provider is not configured for model entity.');
        }

        $directory = ($this->sqlDirectoryProvider)($this);
        if (!is_string($directory)) {
            $actual = is_object($directory) ? get_class($directory) : gettype($directory);
            throw new \UnexpectedValueException('Entity SQL directory provider returned "' . $actual . '".');
        }

        return $directory;
    }

    private function entityDependencyList(mixed $provider, string $label): array
    {
        if (!is_callable($provider)) {
            return [];
        }

        $dependencies = $provider($this);
        if (!is_array($dependencies)) {
            $actual = is_object($dependencies) ? get_class($dependencies) : gettype($dependencies);
            throw new \UnexpectedValueException($label . ' returned "' . $actual . '".');
        }

        return $dependencies;
    }

    private function namespaceResolver(): callable
    {
        if (!isset($this->namespaceResolver)) {
            $this->namespaceResolver = $this->defaultNamespaceResolver();
        }

        return $this->namespaceResolver;
    }

    private function modelClassExists(string $className): bool
    {
        if (!isset($this->modelClassExists)) {
            throw new \RuntimeException('Model class availability checker is not configured for model entity.');
        }

        return ($this->modelClassExists)($className);
    }

    private function defaultNamespaceResolver(): \Closure
    {
        return \Closure::fromCallable(
            static fn(object|string $object, int $depth = 1): string => self::nativeNamespaceName($object, $depth)
        );
    }

    /**
     * @throws \fan\project\exception\model\entity\fatal
     */
    protected function _getClassName(string $key): string
    {
        $name = $this->getName();
        if (empty($name)) {
            $className = '';
        } else {
            $prefix = $this->entityNamespacePrefix();
            if (empty($prefix)) {
                throw $this->createModelEntityFatalException('In config prefix doesn\'t set for "' . $key . '".');
            }

            $className = $prefix . $name . '\\' . $key;
        }
        if (empty($className) || !$this->modelClassExists($className)) {
            $className = '\fan\project\base\model\\' . $key;
        }

        $reflection = $this->reflectionClass($className);
        do {
            if ($reflection->getName() === 'fan\core\base\model\\' . $key) {
                return $className;
            }
            $reflection = $reflection->getParentClass();
        } while (!empty($reflection));

        throw $this->createModelEntityFatalException('Class "' . $className . '" must be instance of "\fan\core\base\model\\' . $key . '".');
    }

    /**
     * @throws \fan\project\exception\model\entity\fatal
     */
    protected function _getSqlAsString(string|object $query, mixed $param): array
    {
        if (is_object($query) && method_exists($query, 'assemble') && method_exists($query, 'getAdjustedParam')) {
            return [$query->assemble($param), $query->getAdjustedParam()];
        } elseif (!is_string($query)) {
            return [$query, $param];
        }
        throw $this->createModelEntityFatalException('Incorrect format of SQL-request.');
    }

    protected function _getRowByData(?array &$data = null): row
    {
        return empty($data) ? $this->createRow() : $this->createRow($data);
    }
}
