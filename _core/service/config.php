<?php
declare(strict_types=1);

namespace fan\core\service;
use \fan\core\service\config\row as row;
use fan\core\base\model\entity as model_entity;
use fan\core\base\service;
use fan\core\base\service\multi;
use fan\core\service\config\base;

/**
 * Configuration manager service
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
 * @version of file: 05.02.002 (31.03.2014)
 */
final class config extends multi
{
    private ?string $configType = null;
    private ?string $sourceType = null;

    /**
     * @var \fan\core\service\config\row Row of configuration data
     */
    private ?object $confData = null;

    /**
     * @var callable|null
     */
    private $configFactory = null;

    /**
     * @var callable|null
     */
    private $configCacheFactory = null;

    private ?object $configRuntime = null;
    private ?object $configState = null;

    /**
     * @var callable|null
     */
    private $phpArrayFileLoader = null;

    /**
     * @var callable|null
     */
    private $configRowFactory = null;

    private ?object $sourceFileMetadata = null;

    private ?object $sourceFileStorage = null;

    private mixed $shortClassNameResolver = null;

    /**
     * @throws \fan\project\exception\service\fatal
     */
    public function __construct(
        string $configType,
        string $sourceType,
        ?callable $configFactory = null,
        ?callable $configCacheFactory = null,
        ?object $configState = null,
        ?object $runtime = null,
        ?object $serviceBootstrapRuntime = null,
        ?object $serviceConfigurator = null,
        ?callable $serviceCacheFactory = null,
        ?callable $phpArrayFileLoader = null,
        ?callable $configRowFactory = null,
        ?object $sourceFileMetadata = null,
        ?object $sourceFileStorage = null,
        ?callable $shortClassNameResolver = null
    )
    {
        $this->setConfigDependencies($configFactory, $configCacheFactory, $runtime, $phpArrayFileLoader, $configRowFactory, $sourceFileMetadata, $sourceFileStorage, $shortClassNameResolver);
        $this->configState = $configState;
        $this->setServiceDependencies($serviceBootstrapRuntime ?? $runtime, $serviceConfigurator ?? $this, $serviceCacheFactory);
        $this->configType = $configType;
        $this->sourceType = $sourceType;

        $this->state()->setInstance($configType, $this);

        $method = $configType === 'service' ? '_initServiceConfig' : '_initOtherConfig';
        $this->$method();

        parent::__construct(true, $serviceBootstrapRuntime ?? $runtime, $serviceConfigurator ?? $this, $serviceCacheFactory);
    }

    public function mergeByApp(string $appName): void
    {
        foreach ($this->state()->getAppDepended() as $k => $v) {
            $confFile = str_replace('{APP_NAME}', $appName, $v);
            $this->configService((string)$k)->_mergeConfig($confFile, true, false);
        }
    }

    // ======== Main Interface methods ======== \\
    public function setConfigDependencies(
        ?callable $configFactory = null,
        ?callable $configCacheFactory = null,
        ?object $runtime = null,
        ?callable $phpArrayFileLoader = null,
        ?callable $configRowFactory = null,
        ?object $sourceFileMetadata = null,
        ?object $sourceFileStorage = null,
        ?callable $shortClassNameResolver = null
    ): static
    {
        $this->configFactory = $configFactory;
        $this->configCacheFactory = $configCacheFactory;
        $this->configRuntime = $runtime;
        if ($phpArrayFileLoader !== null) {
            $this->phpArrayFileLoader = $phpArrayFileLoader;
        }
        if ($configRowFactory !== null) {
            $this->configRowFactory = \Closure::fromCallable($configRowFactory);
        }
        if ($sourceFileMetadata !== null) {
            $this->sourceFileMetadata = $sourceFileMetadata;
        }
        if ($sourceFileStorage !== null) {
            $this->sourceFileStorage = $sourceFileStorage;
        }
        if ($shortClassNameResolver !== null) {
            $this->shortClassNameResolver = \Closure::fromCallable($shortClassNameResolver);
        }

        return $this;
    }

    public function get(string $name, string|array|null $key = null): mixed
    {
        $conf = $this->confData[$name];
        return empty($conf) ? null : (is_null($key) ? $conf : $conf->get($key));
    }

    public function getSrc(string $name, mixed $key = null): mixed
    {
        $conf = $this->confData[$name];
        if (empty($conf)) {
            return null;
        }
        if (is_null($key)) {
            return $conf->getSources();
        }
        $subConf = $conf->get($key);
        return empty($subConf) ? null : $subConf->getSources();
    }

    /**
     * @param string $value Value that should be applied or transformed.
     */
    public function set(string $name, string $key, string $value, bool $rewriteExisting = true): static
    {
        $conf = $this->confData[$name];
        if (empty($conf)) {
            $conf = $this->confData->set($name, []);
        }
        $conf->set($key, $value, $rewriteExisting, true);
        return $this;
    }

    public function merge(array|row $data, bool $priority = true): static
    {
        if (!is_array($data) && !$this->_isRow($data)) {
            throw $this->createServiceFatalException('Incorrect data for merge configs');
        }
        if (!empty($data)) {
            $this->confData->mergeData($data, $priority);
        }
        return $this;
    }

    public function reset(mixed $name = null, mixed $key = null): static
    {
        if (is_null($name)) {
            $this->confData->reset(null);
        } else {
            $conf = $this->confData[$name];
            if ($this->_isRow($conf)) {
                if (is_array($key)) {
                    $key = array_pop($key);
                    if (!empty($key)) {
                        $conf = $conf->get($key);
                    }
                    if ($this->_isRow($conf)) {
                        $conf->reset($key);
                    }
                } else {
                    $conf->reset($key);
                }
            }
        }
        return $this;
    }

    public function getConfigType(): ?string
    {
        return $this->configType;
    }

    public function getServiceConfig(service $service): row
    {
        $name = $this->shortClassName($service);
        if (empty($this->confData)) {
            throw $this->createServiceFatalException('Data row isn\'t set for config "' . $this->configType . '"');
        }
        if (!$this->confData[$name]) {
            $this->confData->set($name, []);
        }
        $this->confData[$name]->setServiceOwner($service);
        return $this->confData[$name];
    }
    /**
     * @throws \fan\core\exception\error500
     */
    public function getControllerConfig(mixed $ctrl, string $name): row
    {
        if (empty($this->confData)) {
            throw $this->createServiceFatalException('Data row isn\'t set for config "' . $name . '"');
        }
        if (!$this->confData[$name]) {
            $this->confData->set($name, []);
        }
        if (is_object($ctrl)) {
            $this->confData[$name]->setPlainOwner($ctrl, $name);
        }
        return $this->confData[$name];
    }
    public function getEntityConfig(model_entity $entity, ?string $name = null): row
    {
        if (is_null($name)) {
            $name = $entity->getTableName();
        }

        $ettConf    = $this->confData['entity'];
        $commonConf = $this->confData['common'];

        if (is_null($ettConf->get($name))) {
            $ettConf->set($name, []);
        }
        $ettConf[$name]->setEntityOwner($entity, $name);
        if (!empty($commonConf)) {
            $ettConf[$name]->mergeData($commonConf, false);
        }
        return $ettConf[$name];
    }

    public function createConfigFatalException(string $message, int $code = E_USER_ERROR, ?\Throwable $previous = null): \Throwable
    {
        return $this->createServiceFatalException($message, $code, $previous);
    }

    // ======== Private/Protected methods ======== \\
    protected function _initServiceConfig(): static
    {
        $this->state()->setCache($this->configCache());

        $this->confData = $this->configRow($this->_getData('service'));
        $this->confData->setFacade($this);

        $thisConfig = $this->getServiceConfig($this);
        $this->state()->setThisConfig($thisConfig);
        $this->config = $thisConfig;

        if ($this->config['app_file']) {
            $this->state()->setAppDepended($this->config['app_file']->toArray());
        }
        $this->_subscribeForService('application', 'setAppName', [$this, 'mergeByApp']);

        return $this;
    }

    protected function _initOtherConfig(): static
    {
        if (empty($this->state()->getThisConfig())) {
            $this->configService('service');
        }
        $this->config = clone $this->state()->getThisConfig();

        $fileName       = $this->getConfig(['file', $this->configType], $this->configType);
        $this->confData = $this->configRow($this->_getData($fileName));
        $this->confData->setFacade($this);

        return $this;
    }

    protected function _getData(string $fileName, bool $checkExist = true): mixed
    {
        $engine   = $this->_getConfigEngine();
        $filePath = $engine->getFilePath($fileName, $checkExist);
        $cache = $this->state()->getCache();
        if (!empty($cache)) {
            $data = $cache->get($fileName);
            if (!empty($data) && $cache->checkSourceFile($fileName, $filePath)) {
                return $data;
            }
        }

        $data = $engine->loadFile($filePath, $this->configType);
        if (!empty($data) && !empty($cache)) {
            $cache->set($fileName, $data);
            $cache->setExtraMeta($fileName, 'file_size', $this->sourceFileMetadata()->size($filePath));
        }

        return $data;
    }

    protected function _setConfig(): static
    {
        return $this;
    }

    protected function _getConfigEngine(): base
    {
        $type = $this->sourceType;
        $engine = $this->state()->getEngine($type);
        if ($engine === null) {
            $engine = parent::_getEngine($type, true);
            if (empty($engine)) {
                throw $this->createServiceFatalException('Unknown engine type!');
            }
            $engine->setDirPath(
                $this->configRuntime()->getGlobalPath('config_source', '{PROJECT_DIR}/conf')
            );
            $engine->setFileStorage($this->sourceFileStorage());
            if (method_exists($engine, 'setPhpArrayFileLoader')) {
                $engine->setPhpArrayFileLoader($this->phpArrayFileLoader());
            }
            $this->state()->setEngine($type, $engine);
        }
        return $engine;
    }

    protected function _mergeConfig(string $fileName, bool $resetConf, bool $checkExist): static
    {
        if ($resetConf) {
            $this->reset();
        }
        $this->confData->mergeData($this->_getData($fileName, $checkExist), $resetConf);
        return $this;
    }

    protected function _isRow(mixed $obj): bool
    {
        return is_object($obj) && $obj instanceof row;
    }

    private function configService(string $configType, string $sourceType = 'arr'): object
    {
        if ($this->configFactory !== null) {
            return ($this->configFactory)($configType, $sourceType);
        }

        throw new \RuntimeException('Config factory is not configured for config service.');
    }

    private function configCache(): ?object
    {
        if ($this->configCacheFactory !== null) {
            $cache = ($this->configCacheFactory)();
            return is_object($cache) ? $cache : null;
        }

        throw new \RuntimeException('Config cache factory is not configured for config service.');
    }

    private function configRuntime(): object
    {
        if ($this->configRuntime !== null) {
            return $this->configRuntime;
        }

        throw new \RuntimeException('Bootstrap runtime service is not configured for config service.');
    }

    private function phpArrayFileLoader(): callable
    {
        if (is_callable($this->phpArrayFileLoader)) {
            return $this->phpArrayFileLoader;
        }

        throw new \RuntimeException('PHP-array file loader is not configured for config service.');
    }

    private function configRow(mixed $data): row
    {
        if (!is_callable($this->configRowFactory)) {
            throw new \RuntimeException('Config row factory is not configured for config service.');
        }

        $row = ($this->configRowFactory)($data);
        if (!$row instanceof row) {
            $actual = is_object($row) ? get_class($row) : gettype($row);
            throw new \UnexpectedValueException('Config row factory returned "' . $actual . '".');
        }

        return $row;
    }

    private function state(): object
    {
        if ($this->configState !== null) {
            return $this->configState;
        }

        throw new \RuntimeException('Config state is not configured for config service.');
    }

    private function sourceFileMetadata(): object
    {
        if ($this->sourceFileMetadata === null) {
            throw new \RuntimeException('Source file metadata dependency is not configured for config service.');
        }

        return $this->sourceFileMetadata;
    }

    private function sourceFileStorage(): object
    {
        if ($this->sourceFileStorage === null) {
            throw new \RuntimeException('Source file storage dependency is not configured for config service.');
        }

        return $this->sourceFileStorage;
    }

    private function shortClassName(object|string $object): string
    {
        if (!is_callable($this->shortClassNameResolver)) {
            throw new \RuntimeException('Short class-name resolver is not configured for config service.');
        }

        $className = ($this->shortClassNameResolver)($object);
        if (!is_string($className) || $className === '') {
            throw new \UnexpectedValueException('Short class-name resolver must return a non-empty string.');
        }

        return $className;
    }

}
