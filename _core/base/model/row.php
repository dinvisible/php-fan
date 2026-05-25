<?php

declare(strict_types=1);

namespace fan\core\base\model;
use fan\core\base\model\entity;
use fan\core\base\model\row as model_row;
use fan\core\base\model\rowset;
use fan\core\di\container_interface;

/**
 * Description of row
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
 * @version of file: 05.02.011 (03.10.2015)
 */
class row implements \ArrayAccess
{
    /**
     * Saved data
     * @var array
     */
    protected array $srcData = [];
    /**
     * Saved data
     * @var array
     */
    protected array $data = [];
    /**
     * Changed fields
     * @var array
     */
    protected array $changed = [];

    /**
     * Object of Entity Class
     * @var \fan\core\base\model\entity
     */
    protected ?object $entity = null;
    /**
     * Object of Rowset Class
     * @var \fan\core\base\model\rowset
     */
    protected ?object $rowset = null;

    /**
     * Field Info
     * @var array
     */
    protected array $fieldInfo = [];

    /**
     * This property true if data is load
     * @var boolean
     */
    protected bool $isDataLoad = false;
    /**
     * This property true if Init Id Only without Loading (Used for update part/full of Row-data)
     * @var boolean
     */
    protected bool $initIdOnly = false;

    protected ?string $currentLocal = null;
    protected ?string $defaultLocal = null;

    /**
     * Flag for show error message
     * @var boolean
     */
    protected bool $showError = true;

    private mixed $errorFactory = null;

    private mixed $localeFactory = null;

    private mixed $entityFactory = null;

    private \Closure $snapshotEncoder;

    private \Closure $snapshotDecoder;

    private mixed $modelRowExceptionFactory = null;

    private \Closure $arrayLikeChecker;

    private \Closure $namespaceResolver;

    public function __construct(
        entity $entity,
        array &$data = [],
        ?rowset $rowset = null,
        ?container_interface $serviceContainer = null,
        ?callable $snapshotEncoder = null,
        ?callable $snapshotDecoder = null,
        ?callable $modelRowExceptionFactory = null,
        ?callable $arrayLikeChecker = null,
        ?callable $namespaceResolver = null
    )
    {
        $this->entity = $entity;
        $this->rowset = $rowset;
        $this->snapshotEncoder = \Closure::fromCallable(
            $snapshotEncoder ?? static function (mixed $state): string {
                throw new \RuntimeException('Snapshot encoder is not configured for model row.');
            }
        );
        $this->snapshotDecoder = \Closure::fromCallable(
            $snapshotDecoder ?? static function (string $payload, mixed $default = null): mixed {
                throw new \RuntimeException('Snapshot decoder is not configured for model row.');
            }
        );
        if ($modelRowExceptionFactory !== null) {
            $this->modelRowExceptionFactory = $modelRowExceptionFactory;
        }
        $this->arrayLikeChecker = \Closure::fromCallable(
            $arrayLikeChecker ?? static fn(mixed $value): bool => is_array($value) || $value instanceof \ArrayAccess
        );
        $this->namespaceResolver = \Closure::fromCallable(
            $namespaceResolver ?? static fn(object|string $object, int $depth = 1): string => self::nativeNamespaceName($object, $depth)
        );
        $this->setDependenciesFromEntityService($entity);
        $this->_fixLoadedData($data);

        $this->_restoreProperties();
    }

    public function setRowDependencies(
        ?callable $errorFactory = null,
        ?callable $localeFactory = null,
        ?callable $entityFactory = null,
        ?callable $snapshotEncoder = null,
        ?callable $snapshotDecoder = null,
        ?callable $modelRowExceptionFactory = null,
        ?callable $arrayLikeChecker = null,
        ?callable $namespaceResolver = null
    ): static {
        if ($errorFactory !== null) {
            $this->errorFactory = $errorFactory;
        }
        if ($localeFactory !== null) {
            $this->localeFactory = $localeFactory;
        }
        if ($entityFactory !== null) {
            $this->entityFactory = $entityFactory;
        }
        if ($snapshotEncoder !== null) {
            $this->snapshotEncoder = \Closure::fromCallable($snapshotEncoder);
        }
        if ($snapshotDecoder !== null) {
            $this->snapshotDecoder = \Closure::fromCallable($snapshotDecoder);
        }
        if ($modelRowExceptionFactory !== null) {
            $this->modelRowExceptionFactory = $modelRowExceptionFactory;
        }
        if ($arrayLikeChecker !== null) {
            $this->arrayLikeChecker = \Closure::fromCallable($arrayLikeChecker);
        }
        if ($namespaceResolver !== null) {
            $this->namespaceResolver = \Closure::fromCallable($namespaceResolver);
        }

        return $this;
    }

    protected function setDependenciesFromEntityService(entity $entity): void
    {
        try {
            $service = $entity->getService();
        } catch (\Throwable) {
            return;
        }
        if (method_exists($service, 'getRowDependencies')) {
            $this->setRowDependencies(...$service->getRowDependencies());
        }
    }

    private function errorService(): object
    {
        return $this->errorFactory !== null ? ($this->errorFactory)() : throw new \RuntimeException('Error service is not configured for model row.');
    }

    private function localeService(): object
    {
        return $this->localeFactory !== null ? ($this->localeFactory)() : throw new \RuntimeException('Locale service is not configured for model row.');
    }

    private function entityService(mixed $collection = 0): object
    {
        return $this->entityFactory !== null ? ($this->entityFactory)($collection) : throw new \RuntimeException('Entity service is not configured for model row.');
    }

    protected function createModelRowFatalException(string $message, ?entity $entity = null, int $code = E_USER_ERROR, ?\Throwable $previous = null): \Throwable
    {
        if (!is_callable($this->modelRowExceptionFactory)) {
            throw new \RuntimeException('Model row exception factory is not configured for model row.');
        }

        $exception = ($this->modelRowExceptionFactory)(
            '\fan\project\exception\model\entity\fatal',
            $entity ?? $this->getEntity(),
            $message,
            $code,
            $previous
        );
        if (!$exception instanceof \Throwable) {
            $actual = is_object($exception) ? get_class($exception) : gettype($exception);
            throw new \UnexpectedValueException('Model row exception factory returned "' . $actual . '".');
        }

        return $exception;
    }

    // ======== Methods for redefine in children classes ======== \\

    protected function _runAfterLoad(): void
    {
    }
    protected function _runAfterLoadFail(): void
    {
    }
    protected function _runBeforeInsert(): void
    {
    }
    protected function _runBeforeUpdate(): void
    {
    }
    protected function _runAfterInsert(array $changed): void
    {
    }
    protected function _runAfterUpdate(array $changed): void
    {
    }
    protected function _runAfterSave(array $changed): void
    {
    }
    protected function _runAfterDelete(mixed $delId): void
    {
    }

    // ======== Static methods ======== \\

    // ======== Main Interface methods ======== \\
    /**
     * @param mixed $default Fallback value returned when no explicit value is available.
     */
    public function getConfig(?string $key = null, mixed $default = null): mixed
    {
        return $this->getEntity()->getConfig($key, $default);
    }

    public function loadById(mixed $rowId, bool $idIsEncrypt = false): mixed
    {
        $param = is_null($rowId) ? null : $this->getEntity()->getParamById($rowId, (bool)$idIsEncrypt);
        $this->loadByParam($param, 0, null);
        return $this;
    }

    public function loadByParam(array|object|null $param, int|float $offset = 0, ?string $orderBy = null): static
    {
        $ett = $this->getEntity();
        if (!empty($this->changed)) {
            $name = $ett->getName();
            throw new \LogicException('Load new data for changed row. Entity "' . $name . '". Id=' . $this->getId(false));
        } elseif ($this->isDataLoad) {
            $name = $ett->getName();
            throw new \LogicException('Load new data for loaded row. Entity "' . $name . '". Id=' . $this->getId(false));
        }

        if (is_null($param)) {
            $data = [];
        } else {
            $data =& $ett->getDataByParam($param, 1, $offset, $orderBy, true);
        }
        $this->_fixLoadedData($data);
        return $this;
    }

    /**
     * @throws \fan\project\exception\model\entity\fatal
     */
    public function initIdOnly(mixed $rowId): static
    {
        if ($this->isDataLoad) {
            throw $this->createModelRowFatalException('Call "initIdOnly"-method for loaded data!');
        }
        $primeryKey = $this->getEntity()->description->getPrimeryKey();
        if (is_string($primeryKey)) {
            $this->set($primeryKey, $rowId, false);
        } else {
            foreach ($primeryKey as $k) {
                $this->set($k, $rowId[$k], false);
            }
        }
        $this->initIdOnly = true;
        return $this;
    }

    /**
     * @param mixed $defaultVal Fallback value returned when no explicit value is available.
     */
    public function get(string|int|float $fieldName, mixed $defaultVal = null, bool $allowException = true): mixed
    {
        $fieldName = (string)$fieldName;
        $fullFieldName = $fieldName;
        if ($fieldName[0] === '{' && substr($fieldName, -1) === '}') {
            $fieldName = substr($fieldName, 1, -1);
            if (array_key_exists($fieldName . $this->_getCurrentLocal(), $this->data)) {
                $fullFieldName = $fieldName . $this->_getCurrentLocal();
            } elseif (array_key_exists($fieldName . $this->_getDefaultLocal(), $this->data)) {
                $fullFieldName = $fieldName . $this->_getDefaultLocal();
            }
        }

        $method = 'get_' . $fullFieldName;
        if (method_exists($this, $method)) {
            return $this->$method($defaultVal, $allowException);
        }
        if ($fieldName !== $fullFieldName) {
            $method = 'get_' . $fieldName;
            if (method_exists($this, $method)) {
                return $this->$method($defaultVal, $allowException);
            }
        }
        return $this->_getFieldValue($fullFieldName, $defaultVal, $allowException);
    }
    /**
     * @param mixed $defaultVal Fallback value returned when no explicit value is available.
     */
    public function getByLocal(string $name, mixed $defaultVal = null, bool $allowException = true): mixed
    {
        return $this->get('{' . $name . '}', $defaultVal, $allowException);
    }

    /**
     * @param array|entity $value Value that should be applied or transformed.
     */
    public function set(string|int|float $fieldName, mixed $value, bool $allowException = true): static
    {
        $fieldName = (string)$fieldName;
        $fullFieldName = $fieldName;

        if ($fieldName[0] === '{' && substr($fieldName, -1) === '}') {
            $fieldName = substr($fieldName, 1, -1);
            $fullFieldName = $fieldName . $this->_getCurrentLocal();
        }

        // Set main value of field by one of ways
        do {
            $method  = 'set_' . $fullFieldName;
            if (method_exists($this, $method)) {
                $this->$method($value, $allowException);
                break;
            } elseif ($fullFieldName !== $fieldName) {
                $method  = 'set_' . $fieldName;
                if (method_exists($this, $method)) {
                    $this->$method($value, $allowException);
                    break;
                }
            }
            $this->_setFieldValue($fullFieldName, $value, $allowException);
        } while (false);

        // If New row - duplicate value for default local if it isn't set
        if (!$this->isDataLoad && $fullFieldName !== $fieldName) {
            $defaultFieldName = $fieldName . $this->_getDefaultLocal();
            if (!isset($this->data[$defaultFieldName])) {
                $this->_setFieldValue($defaultFieldName, $this->data[$fullFieldName], $allowException);
            }
        }

        return $this;
    }

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    public function setByLocal(string $name, mixed $value = null, bool $allowException = true): static
    {
        return $this->set('{' . $name . '}', $value, $allowException);
    }

    public function toArray(): array
    {
        return $this->getFields(null, true);
    }
    public function getFields(mixed $keys = null, bool $allExists = true): array
    {
        if (is_null($keys)) {
            if ($allExists) {
                $keys = array_keys($this->data);
            } else {
                $info = $this->_getFullFieldsInfo();
                $keys = empty($info) ? [] : array_keys($info);
            }
        } elseif (!$this->isArrayLike($keys)) {
            if (!is_scalar($keys)) {
                throw $this->createModelRowFatalException('Incorrect Field Keys.');
            }
            $keys = [$keys];
        }

        $result = [];
        foreach ($keys as $k) {
            $result[$k] = $this->get($k, null, $this->isDataLoad);
        }
        return $result;
    }

    private function isArrayLike(mixed $value): bool
    {
        return ($this->arrayLikeChecker)($value);
    }

    protected function namespaceName(object|string $object, int $depth = 1): string
    {
        $namespace = ($this->namespaceResolver)($object, $depth);
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

    public function setFields(mixed $fields, bool $isSave = false): static
    {
        if (is_array($fields)) {
            foreach ($fields as $fieldName => $value) {
                $this->set($fieldName, $value);
            }
        }
        if ($isSave) {
            $this->save();
        }
        return $this;
    }

    public function getTopRow(string $byField, bool $logEmptyVal = false): ?model_row
    {
        $err = $this->errorService();
        /* @var $err \fan\core\service\error */
        $errHeader = 'Error while get Top Row';
        $val = $this->get($byField, null, false);
        if (empty($val)) {
            if ($logEmptyVal) {
                $err->logErrorMessage('Value for field "' . $byField . '" is empty', $errHeader);
            }
            return null;
        }

        $ett = $this->entity;
        $tmp = $ett->description->relations;
        $rel = null;
        foreach ($tmp as $v) {
            if ((string)$v['field'] === (string)$byField) {
                $rel = $v;
                break;
            }
        }
        if (empty($rel)) {
            $err->logErrorMessage('Incorrect linked field "' . $byField . '"', $errHeader);
            return null;
        }

        $topEtt = $ett->getService()->getEntityByTable($rel['ref_table'], $ett->getConnectionName());
        if (empty($topEtt)) {
            $err->logErrorMessage('Linked entity for field "' . $byField . '" is not found', $errHeader);
            return null;
        }
        return $topEtt->getRowByParam([$v['ref_field'] => $val]);
    }

    public function getBottomRowset(string|int|float $tableName, int|float $qtt = -1, int|float $offset = -1, string $orderBy = ''): ?rowset
    {
        $err = $this->errorService();
        /* @var $err \fan\core\service\error */
        $errHeader = 'Error while get Bottom Rowset';

        $curEtt    = $this->getEntity();
        $bottomEtt = $curEtt->getService()->getEntityByTable((string)$tableName, $curEtt->getConnectionName());
        if (empty($bottomEtt)) {
            $err->logErrorMessage('Can\'t get entity for table "' . $tableName . '"', $errHeader);
            return null;
        }

        $tmp = $bottomEtt->description->relations;
        $rel = null;
        foreach ($tmp as $v) {
            if ((string)$v['ref_table'] === (string)$curEtt->getTableName()) {
                $rel = $v;
                break;
            }
        }
        if (empty($rel)) {
            $err->logErrorMessage('Incorrect linked table "' . $tableName . '"', $errHeader);
            return null;
        }

        return $bottomEtt->getRowsetByParam(
            [$v['field'] => $this->getId()],
            is_numeric($qtt) ? $qtt + 0 : -1,
            is_numeric($offset) ? $offset + 0 : -1,
            (string)$orderBy
        );
    }

    public function revert(): static
    {
        foreach ($this->srcData as $fieldName => $value) {
            $this->data[$fieldName] = $value;
        }
        $this->changed = [];
        return $this;
    }

    public function save(): static
    {
        $changed = $this->changed;
        if (!empty($changed)) {
            if ($this->isDataLoad || $this->initIdOnly) {
                $this->_runBeforeUpdate();
                $this->_updateRow();
                $this->_runAfterUpdate($changed);
            } else {
                $this->_runBeforeInsert();
                $this->_insertRow();
                $this->_runAfterInsert($changed);
            }
            $this->_runAfterSave($changed);
        }
        return $this;
    }

    public function delete(): bool
    {
        if ($this->isDataLoad) {
            $ett   = $this->getEntity();
            $delId = $this->getId(false, true);
            if ($delId) {
                $designer = $ett->getDesigner('delete');
                /* @var $designer \fan\core\service\entity\designer\delete */
                $query    = $designer->setDeleteByParam($this->getId(false, true, true))->assemble();
                $adjParam = $designer->getAdjustedParam();
                $connect = $ett->getConnection();
                $connect->execute($query, $adjParam);
                if ($connect->isError()) {
                    return false;
                }

                $this->_resetProperty(false);
                $this->_runAfterDelete($delId);
                return true;
            } else {
                throw $this->createModelRowFatalException('Can\'t get source ID for delete row.');
            }
        }
        return false;
    }

    public function getId(bool $allowException = true, bool $useSourceValue = false, bool $alwaysArray = false): mixed
    {
        $idKey = $this->getEntity()->description->getPrimeryKey();
        if (is_array($idKey)) {
            $result = [];
            foreach ($idKey as $k) {
                $result[$k] = $useSourceValue && isset($this->srcData[$k]) ? $this->srcData[$k] : $this->get($k, null, $allowException);
            }
            return $result;
        }
        $result = $useSourceValue && isset($this->srcData[$idKey]) ? $this->srcData[$idKey] : $this->_getFieldValue($idKey, null, $allowException);
        return $alwaysArray ? [$idKey => $result] : $result;
    }

    /**
     * @throws \fan\project\exception\model\entity\fatal
     */
    public function setId(mixed $idVal): void
    {
        $idKey = $this->getEntity()->description->getPrimeryKey();
        if (is_array($idKey)) {
            foreach ($idVal as $k => $v) {
                if (!in_array($k, $idKey)) {
                    throw $this->createModelRowFatalException('Incorrect id name (as array)!');
                }

                $this->_setFieldValue($k, $v);
            }
        } else {
            $this->_setFieldValue($idKey, $idVal);
        }
    }

    public function getEntity(): entity
    {
        return $this->entity;
    }
    public function getRowset(): ?rowset
    {
        return $this->rowset;
    }

    public function checkIsLoad(): bool
    {
        return $this->isDataLoad;
    }

    public function getSrcFields(): array
    {
        return $this->srcData;
    }

    public function getChangedElm(): array
    {
        return $this->changed;
    }

    public function getDefaultValue(): array
    {
        $defaultValue = [];
        foreach ($this->_getFullFieldsInfo() as $k => $v) {
            if (!$v['auto_increment']) {
                $defaultValue[$k] = $v['default'];
            }
        }
        return $defaultValue;
    }

    public function getDebugInfo(): array
    {
        return [
            'entity_name' => $this->getEntity()->getName(true),
            'data'        => $this->getFields(),
            'src_data'    => $this->srcData,
            'changed'     => $this->changed,
            'flags'       => [
                'is_load'    => $this->isDataLoad,
                'show_error' => $this->showError,
                'local'      => $this->currentLocal
            ],
            'connection' => $this->getEntity()->getConnectionName(),
        ];
    }

    public function setShowError(bool $showError): static
    {
        $this->showError = !empty($showError);
        return $this;
    }

    // ======== Private/Protected methods ======== \\
    protected function _fixLoadedData(array &$data): bool
    {
        if (empty($data)) {
            $this->_resetProperty();
        } else {
            $this->isDataLoad = true;
            $this->srcData    =  $data;
            $this->data       =& $data;
            $this->changed    = [];
        }
        return $this->isDataLoad;
    }

    protected function _insertRow(): static
    {
        if (empty($this->changed)) {
            return $this;
        }

        $this->_setDefault();
        if (empty($this->data)) {
            return $this;
        }

        $ett      = $this->getEntity();
        $connect  = $ett->getConnection();
        $designer = $ett->getDesigner('insert');
        /* @var $designer \fan\core\service\entity\designer\insert */
        $query    = $designer->setInsertByParam($this->data)->assemble();
        $adjParam = $designer->getAdjustedParam();
        $connect->execute($query, $adjParam);

        $errMsg = $connect->getErrorMessage();
        if (!$errMsg) {
            foreach ($this->_getFullFieldsInfo() as $k => $v) {
                if ($v['auto_increment'] && empty($this->data[$k])) {
                    $this->data[$k] = $connect->getInsertId();
                } elseif (!isset($this->data[$k])) {
                    $this->data[$k] = null;
                }
            }

            $this->isDataLoad = true;
            $this->changed    = [];

            //ToDo: if ($this->cacheIt) {}
        } elseif ($this->showError) {
            $this->errorService()->logErrorMessage($errMsg, 'Data isn\'t inserted.', 'Entity name: ' . $ett->getName(true) . "\n\n" . $query . "\nData: " . var_export($adjParam, true));
        }
        return $this;
    }

    /**
     * @throws \fan\project\exception\model\entity\fatal
     */
    protected function _updateRow(): static
    {
        if (empty($this->changed)) {
            return $this;
        } // check rows

        $ett     = $this->getEntity();
        $idValue = $this->getId(false, true);
        if (empty($idValue)) {
            throw $this->createModelRowFatalException('Update impossible. ID isn\'t set!', $ett);
        }

        $connect  = $ett->getConnection();
        $designer = $ett->getDesigner('update');
        /* @var $designer \fan\core\service\entity\designer\update */
        $query    = $designer->setUpdateByParam($this->changed, $this->getId(false, true, true))->assemble();
        $adjParam = $designer->getAdjustedParam();
        $connect->execute($query, $adjParam);

        $errMsg = $connect->getErrorMessage();
        if (!$errMsg) {
            $this->changed = [];
        } elseif ($this->showError) {
            $this->errorService()->logErrorMessage($errMsg, 'Data isn\'t updated.', 'Entity name: ' . $ett->getName(true) . "\n\n" . $query . "\nData: " . var_export($adjParam, true));
        }
        return $this;
    }

    /**
     * @throws \fan\project\exception\model\entity\fatal
     */
    protected function _getFieldInfo(string $fieldName, bool $allowException = true, bool $forse = false): array
    {
        $fieldInfo = $this->_getFullFieldsInfo($forse);
        if (!isset($fieldInfo[$fieldName])) {
            $errorMessage  = 'Incorrect field name "' . $fieldName . '" for ';
            $errorMessage .= empty($this->entity) ? 'unknown table.' : 'table "' . $this->entity->getTableName() . '".';
            if ($allowException) {
                throw $this->createModelRowFatalException($errorMessage);
            }
            throw new \OutOfBoundsException($errorMessage);
        }
        return $fieldInfo[$fieldName];
    }

    protected function _getFullFieldsInfo(bool $forse = false): array
    {
        if (empty($this->fieldInfo) || $forse) {
            $this->fieldInfo = $this->getEntity()->description->getFields($forse);
        }
        return $this->fieldInfo;
    }

    protected function _isStringType(mixed $type): bool
    {
        return in_array(strtolower((string)$type), ['char', 'varchar', 'blob', 'text', 'mediumblob', 'mediumtext', 'longblob']);
    }

    protected function _isNumberType(mixed $type): bool
    {
        return in_array(strtolower((string)$type), ['tinyint', 'bit', 'bool', 'smallint', 'mediumint', 'int', 'integer', 'bigint', 'float', 'double', 'decimal', 'dec']);
    }


    /**
     * @param mixed $defaultVal Fallback value returned when no explicit value is available.
     */
    protected function _getFieldValue(string|int|float $fieldName, mixed $defaultVal = null, bool $allowException = true): mixed
    {
        $fieldName = (string)$fieldName;
        if ($this->initIdOnly || $allowException) {
            $this->_checkGetWrongValue($fieldName);
        }
        return isset($this->data[$fieldName]) ? $this->data[$fieldName] : $defaultVal;
    }

    /**
     * @param array|entity $value Value that should be applied or transformed.
     */
    protected function _setFieldValue(string|int|float $fieldName, mixed $value, bool $allowException = true): static
    {
        $fieldName = (string)$fieldName;
        $fieldInfo = $this->_getFieldInfo($fieldName, $allowException);
        if ($fieldInfo) {
            $isNumber = $this->_isNumberType($fieldInfo['type']);
            $isString = $this->_isStringType($fieldInfo['type']);

            if (is_null($value) && (!$fieldInfo['null'] && !$fieldInfo['auto_increment'])) {
                $value = $isNumber ? 0 : ($isString ? '' : null);
            } elseif ($isString) {
                if (is_array($value)) {
                    $this->errorService()->logErrorMessage('Value of field "' . $fieldName . '" can\'t be set as Array', 'Error set value of row', '', true, false);
                    $value = '';
                } else {
                    $value = (string)$value;
                }
                if (isset($fieldInfo['length'])) {
                    $isUtf8 = (string)$fieldInfo['charset'] === 'utf8';
                    $lengthFunction = $isUtf8 ? 'mb_strlen' : 'strlen';
                    $substringFunction = $isUtf8 ? 'mb_substr' : 'substr';
                    if ($lengthFunction($value) > $fieldInfo['length']) {
                        $value = $substringFunction($value, 0, $fieldInfo['length']);
                        //ToDo: Notify about truncated data, by Config parameter
                    }
                }
            }

            $currentValue = $this->data[$fieldName] ?? null;
            $isChanged = $isNumber
                ? (float)$currentValue !== (float)$value
                : ($isString ? (string)$currentValue !== (string)$value : $currentValue !== $value);
            if (!array_key_exists($fieldName, $this->data) || $isChanged) {
                $this->changed[$fieldName] = $value;
            }
            $this->data[$fieldName] = $value;
        }
        return $this;
    }
    protected function _setDefault(): static
    {
        foreach ($this->getDefaultValue() as $k => $v) {
            // ToDo: Set default value for enum if it is not null
            if ((!isset($this->data[$k]) || is_null($this->data[$k])) && !is_null($v)) {
                $this->data[$k] = $v;
            }
        }
        return $this;
    }

    protected function _resetProperty(bool $full = true): static
    {
        $this->isDataLoad = false;
        if ($full) {
            $this->srcData = [];
        }
        $this->data    = [];
        $this->changed = [];

        return $this;
    }

    protected function _getCurrentLocal(): string
    {
        if (!$this->currentLocal) {
            $this->currentLocal = '_' . $this->localeService()->getLanguage();
        }
        return $this->currentLocal;
    }


    protected function _getDefaultLocal(): string
    {
        if (!$this->defaultLocal) {
            $this->defaultLocal = '_' . $this->localeService()->getDefaultLanguage();
        }
        return $this->defaultLocal;
    }

    /**
     * @throws \fan\project\exception\model\entity\fatal
     */
    protected function _checkGetWrongValue(string $fieldName): void
    {
        if ($this->initIdOnly && !array_key_exists($fieldName, $this->changed)) {
            throw $this->createModelRowFatalException('This instance has been created for UPDATE DB-row. You can\'t read "' . $fieldName . '" because it contains wrong value now! ');
        }
        if (!array_key_exists($fieldName, $this->data)) {
            throw $this->createModelRowFatalException('Call for unset field "' . $fieldName . '"! ' . "\n Exist fields:" . var_export($this->data, true));
        }
    }

    protected function _restoreProperties(): static
    {
        $this->showError = (bool)$this->getConfig('SHOW_ERROR', $this->showError);
        return $this;
    }

    protected function _convToString(mixed $val): string
    {
        switch (gettype($val)) {
        case 'NULL':
            return 'NULL';
        case 'string':
            return '"' . $val . '"';
        case 'boolean':
            return $val ? 'true' : 'false';
        case 'object':
            return 'object ' . get_class($val);
        }
        return (string)$val;
    }

    // ======== The magic methods ======== \\
    /**
     * Handles dynamic property writes for this current component.
     *
     * @param mixed $value Value that should be applied or transformed.
     */
    public function __set(string $fieldName, mixed $value): void
    {
        $this->set((string)$fieldName, $value);
    }

    /**
     * Handles dynamic property reads for this current component.
     */
    public function __get(string $fieldName): mixed
    {
        return $this->get((string)$fieldName);
    }
    public function __call(string $method, array $args): mixed
    {
        $method = (string)$method;
        if (substr($method, 0, 4) === 'set_') {
            return $this->set(substr($method, 4), isset($args[0]) ? $args[0] : null);
        } elseif (substr($method, 0, 4) === 'get_') {
            return $this->get(substr($method, 4), isset($args[0]) ? $args[0] : null, isset($args[1]) ? $args[1] : true);
        } else {
            throw $this->createModelRowFatalException('Incorrect call of entity method: "' . $method . '"');
        }
    }

    /**
     * Implements PHP magic behavior for this current component.
     */
    public function __toString(): string {
        $ret = '';
        foreach ($this->data as $k => $v) {
            $ret .= empty($ret) ? '' : ', ';
            $ret .= $k . ' => ' . $this->_convToString($v);
        }
        return '(' . $ret . ')';
    }

    // ======== Required Interface methods ======== \\
    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    public function offsetSet(mixed $fieldName, mixed $value): void
    {
        $this->set((string)$fieldName, $value);
    }

    public function offsetExists(mixed $fieldName): bool
    {
        return isset($this->data[$fieldName]);
    }

    public function offsetUnset(mixed $fieldName): void
    {
        $this->set((string)$fieldName, null);
    }

    public function offsetGet(mixed $fieldName): mixed
    {
        return $this->get((string)$fieldName);
    }

    public function serialize(): string
    {
        return ($this->snapshotEncoder())($this->__serialize());
    }

    public function __serialize(): array
    {
        return [
            'mainParam'  => $this->getEntity()->getMainParam(),
            'srcData'    => $this->srcData,
            'data'       => $this->data,
            'changed'    => $this->changed,
            'isDataLoad' => $this->isDataLoad,
            'initIdOnly' => $this->initIdOnly,
        ];
    }

    public function unserialize(string $data): void
    {
        $data = ($this->snapshotDecoder())((string)$data, []);
        if (!is_array($data)) {
            throw new \UnexpectedValueException('Model row snapshot must decode to an array.');
        }

        $this->__unserialize($data);
    }

    public function __unserialize(array $data): void
    {
        $this->restoreSerializedData($data);
    }

    private function restoreSerializedData(array $data): void
    {
        $this->srcData    = $data['srcData'];
        $this->data       = $data['data'];
        $this->changed    = $data['changed'];
        $this->isDataLoad = $data['isDataLoad'];
        $this->initIdOnly = $data['initIdOnly'];

        $param = $data['mainParam'];
        $serv  = $this->entityService($param['collection']);
        $this->entity = empty($param['name']) ?
                $serv->getAnonymous($param['class'], $param['param']) :
                $serv->get($param['name'], $param['param']);
        $this->entity->setConnectionName((string)$param['connection']['name'])->setConnectionKey($param['connection']['key']);

        $this->_restoreProperties();
    }

    private function snapshotEncoder(): callable
    {
        if (!isset($this->snapshotEncoder)) {
            $this->snapshotEncoder = \Closure::fromCallable(
                static function (mixed $state): string {
                    throw new \RuntimeException('Snapshot encoder is not configured for model row.');
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
                    throw new \RuntimeException('Snapshot decoder is not configured for model row.');
                }
            );
        }

        return $this->snapshotDecoder;
    }
}
