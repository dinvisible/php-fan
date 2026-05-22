<?php
declare(strict_types=1);

namespace fan\core\service;
use \fan\core\service\config\row as row;
use fan\project\exception\service\fatal as fatalException;
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
final class config extends \fan\core\base\service\multi
{
    protected static array $instances = [];
    /**
     * Service's Egines by file types
     * @var array
     */
    protected static array $egines = [];
    /**
     * Instance of Cache servise
     * @var \fan\core\service\cache
     */
    protected static ?object $cache = null;

    /**
     * Config of this Service
     * @var \fan\core\service\config\row
     */
    private static ?object $thisConf = null;
    /**
     * List of Application-depended configuration files
     * @var array
     */
    private static array $appDepended = [];

    private ?string $configType = null;
    private ?string $sourceType = null;

    /**
     * @var \fan\core\service\config\row Row of configuration data
     */
    private ?object $confData = null;

    /**
     * @throws \fan\project\exception\service\fatal
     */
    protected function __construct(string $configType, string $sourceType)
    {
        $this->configType = $configType;
        $this->sourceType = $sourceType;

        self::$instances[$configType] = $this;

        $method = $configType === 'service' ? '_initServiceConfig' : '_initOtherConfig';
        $this->$method();

        parent::__construct();
    }

    // ======== Static methods ======== \\
    public static function instance(string $configType = 'service', string $sourceType = 'ini'): static {
        if (!isset(self::$instances[$configType])) {
            new self($configType, $sourceType);
        }

        return self::$instances[$configType];
    }

    public static function mergeByApp(string $appName): void
    {
        foreach (self::$appDepended as $k => $v) {
            $confFile = str_replace('{APP_NAME}', $appName, $v);
            self::instance($k)->_mergeConfig($confFile, true, false);
        }
    }

    // ======== Main Interface methods ======== \\
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

    public function merge(array|\fan\core\service\config\row $data, bool $priority = true): static
    {
        if (!is_array($data) && !$this->_isRow($data)) {
            throw new fatalException($this, 'Incorrect data for merge configs');
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

    public function getServiceConfig(\fan\core\base\service $service): row
    {
        $name = get_class_name($service);
        if (empty($this->confData)) {
            throw new fatalException($this, 'Data row isn\'t set for config "' . $this->configType . '"');
        }
        if (!$this->confData[$name]) {
            $this->confData->set($name, []);
            /*
            // ToDo: Check code above
            $this->confData[$name] = new \fan\project\service\config\row([], $name, $this->confData);
            $this->confData[$name]->setFacade($this);
             */
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
            throw new fatalException($this, 'Data row isn\'t set for config "' . $name . '"');
        }
        if (!$this->confData[$name]) {
            $this->confData->set($name, []);
        }
        if (is_object($ctrl)) {
            $this->confData[$name]->setPlainOwner($ctrl, $name);
        }
        return $this->confData[$name];
    }
    public function getEntityConfig(\fan\core\base\model\entity $entity, ?string $name = null): row
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


    // ======== Private/Protected methods ======== \\
    protected function _initServiceConfig(): static
    {
        self::$cache    = \fan\project\service\cache::configInstance();

        $this->confData = new row($this->_getData('service'));
        $this->confData->setFacade($this);

        self::$thisConf = $this->getServiceConfig($this);
        $this->config   = self::$thisConf;

        if ($this->config['app_file']) {
            self::$appDepended = $this->config['app_file']->toArray();
        }
        $this->_subscribeForService('application', 'setAppName', [get_class($this), 'mergeByApp']);

        return $this;
    }

    protected function _initOtherConfig(): static
    {
        if (empty(self::$thisConf)) {
            config::instance('service');
        }
        $this->config   = clone self::$thisConf;

        $fileName       = $this->getConfig(['file', $this->configType], $this->configType);
        $this->confData = new row($this->_getData($fileName));
        $this->confData->setFacade($this);

        return $this;
    }

    protected function _getData(string $fileName, bool $checkExist = true): mixed
    {
        $engine   = $this->_getConfigEngine();
        $filePath = $engine->getFilePath($fileName, $checkExist);
        if (!empty(self::$cache)) {
            $data = self::$cache->get($fileName);
            if (!empty($data) && self::$cache->checkSourceFile($fileName, $filePath)) {
                return $data;
            }
        }

        $data = $engine->loadFile($filePath, $this->configType);
        if (!empty($data) && !empty(self::$cache)) {
            self::$cache->set($fileName, $data);
            self::$cache->setExtraMeta($fileName, 'file_size', filesize($filePath));
        }

        return $data;
    }

    protected function _setConfig(): static
    {
        return $this;
    }

    protected function _getConfigEngine(): \fan\core\service\config\base
    {
        $type = $this->sourceType;
        if (!isset(self::$egines[$type])) {
            $engine = parent::_getEngine($type, true);
            if (empty($engine)) {
                throw new \fan\project\exception\service\fatal($this, 'Unknown engine type!');
            }
            self::$egines[$type] = $engine;
            $engine->setDirPath(
                \bootstrap::getGlobalPath('config_source', '{PROJECT_DIR}/conf')
            );
        }
        return self::$egines[$type];
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

}
