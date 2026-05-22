<?php
declare(strict_types=1);

namespace fan\core\service\cache;
use fan\project\exception\service\fatal as fatalException;
/**
 * Description of cache-engine base
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
 * @version of file: 05.02.008 (15.09.2015)
 */
abstract class base
{
    protected const JSON_PREFIX = \fan\core\adapter\safe_serializer::JSON_PREFIX;

    /**
     * Facade of service
     * @var \fan\core\service\cache
     */
    protected ?object $facade = null;

    /**
     * Cache engine configuration resolved from service config.
     *
     * @var array<string, mixed>
     */
    protected array $config = [];

    /**
     * Type of cached data
     * @var string
     */
    protected ?string $type = null;

    /**
     * Current cached data
     * @var string
     */
    protected ?string $key = null;

    /**
     * Current cached data
     * @var array
     */
    protected mixed $data = null;
    /**
     * Current meta data
     * @var array
     */
    protected array $metaData = [];

    /**
     * Extra Path for cache directory
     * @var string
     */
    protected ?string $extraPath = null;

    /**
     * Is loaded cached data
     * @var boolean
     */
    protected bool $loaded = false;

    /**
     * Is saved cached data
     * @var boolean
     */
    protected bool $saved = true;

    public function __construct(\fan\core\service\cache $facade, string $type, string $key, array $config)
    {
        $this->setFacade($facade);
        $this->type   = (string)$type;
        $this->key    = (string)$key;
        $this->config = $config;
    }

    public function __destruct()
    {
        if ($this->loaded && !$this->saved) {
            $this->_saveData();
        }
    }

    // ======== Static methods ======== \\

    // ======== Main Interface methods ======== \\

    public function get(mixed $default = null): mixed
    {
        if (!$this->isLoaded()) {
            if ($this->_loadData(false)) {
                $this->loaded = true;
                $this->saved  = true;
            }
        }
        return is_null($this->data) ? $default : $this->data;
    }

    public function set(mixed $value, bool $autoSave): static
    {
        $this->data   = $value;
        $this->loaded = true;
        $this->saved  = false;
        $this->_makeNewMeta();
        if ($autoSave) {
            $this->save();
        }
        return $this;
    }

    /**
     * @throws \fan\core\exception\service\fatal
     */
    public function addMeta(mixed $metaData): static
    {
        if (is_object($metaData) && method_exists($metaData, 'toArray')) {
            $metaData = $metaData->toArray();
        } elseif (!is_array($metaData)) {
            throw new fatalException($this->facade, 'Incorrect cache Meta-date.');
        }
        $this->metaData = array_merge($this->metaData, $metaData);
        return $this;
    }

    public function getMeta(bool $loadMetaOnly): array
    {
        if (!$this->isLoaded()) {
            if ($this->_loadData($loadMetaOnly)) {
                $this->loaded = true;
                $this->saved  = true;
            }
        }
        return $this->metaData;
    }

    public function getExtraMeta(string $param): mixed
    {
        $meta = $this->getMeta(false);
        return isset($meta['extra'][$param]) ? $meta['extra'][$param] : null;
    }

    public function setExtraMeta(string $param, mixed $value): static
    {
        $meta = $this->getMeta(false);
        if (empty($meta)) {
            $this->metaData['extra'] = [];
        }
        $this->metaData['extra'][$param] = $value;
        $this->_saveData();
        return $this;
    }

    public function setLifetime(int $time): static
    {
        $this->metaData['lifetime'] = (int)$time;
        $this->isActual();
        return $this;
    }

    public function setStartLimit(string $dateTime): static
    {
        $this->_checkDateFormat($dateTime);

        $meta = $this->getMeta(false);
        if ($meta['create_date'] < $dateTime) {
            $this->delete();
        }
        return $this;
    }

    public function isActual(): bool
    {
        return $this->_checkActual($this->getMeta(true));
    }

    public function save(): static
    {
        if ($this->loaded && !$this->saved) {
            $this->_saveData();
            $this->saved = true;
        }
        return $this;
    }

    public function delete(): static
    {
        $this->loaded = true;
        $this->saved  = true;
        $this->_deleteData();
        return $this;
    }

    public function setExtraPath(string $extraPath): static
    {
        $this->extraPath = $extraPath;
        return $this;
    }

    public function isLoaded(): bool
    {
        return $this->loaded;
    }

    public function isSaved(): bool
    {
        return $this->saved;
    }

    public function setFacade(\fan\core\base\service $facade): static
    {
        if (empty($this->facade)) {
            $this->facade = $facade;
        }
        return $this;
    }

    // ======== Private/Protected methods ======== \\
    abstract protected function _loadData(bool $loadMetaOnly): bool;

    abstract protected function _saveData(): static;


    protected function _deleteData(): static
    {
        $this->data     = null;
        $this->metaData = [];
        return $this;
    }

    protected function _makeNewMeta(): static
    {
        $this->metaData['data_type']   = strtolower(gettype($this->data));
        $this->metaData['create_date'] = date('Y-m-d H:i:s');
        if (!isset($this->metaData['lifetime'])) {
            $this->metaData['lifetime'] = isset($this->config['LIFETIME']) ? (int)$this->config['LIFETIME'] : 0;
        }
        return $this;
    }

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    protected function _encodePayload(mixed $value): string
    {
        try {
            return \fan\core\adapter\safe_serializer::encodeJson($value);
        } catch (\InvalidArgumentException $e) {
            throw new fatalException($this->facade, 'Cache payload contains data unsupported by JSON.');
        } catch (\JsonException $e) {
            throw new fatalException($this->facade, 'Cache payload isn\'t JSON serializable: ' . $e->getMessage());
        }
    }

    protected function _decodePayload(string $data, string $errorTitle = 'Cache payload decode error'): mixed
    {
        return \fan\core\adapter\safe_serializer::decodeExternalPayload(
            $data,
            null,
            function (string $message) use ($errorTitle): void {
                $this->facade->getContainerService('error')->logErrorMessage($message, $errorTitle, '', true, false);
            }
        );
    }

    protected function _isJsonPayload(string $data): bool
    {
        return \fan\core\adapter\safe_serializer::isJsonPayload($data);
    }

    protected function _decodeLegacyPayload(string $data, string $errorTitle): mixed
    {
        return \fan\core\adapter\safe_serializer::decodeExternalPayload(
            $data,
            null,
            function (string $message) use ($errorTitle): void {
                $this->facade->getContainerService('error')->logErrorMessage($message, $errorTitle, '', true, false);
            }
        );
    }

    protected function _hasUnsupportedJsonValue(mixed $value, int $depth = 0): bool
    {
        if ($depth > 128 || is_object($value) || is_resource($value)) {
            return true;
        }
        if (!is_array($value)) {
            return false;
        }
        foreach ($value as $item) {
            if ($this->_hasUnsupportedJsonValue($item, $depth + 1)) {
                return true;
            }
        }
        return false;
    }

    protected function _checkActual(array $meta, bool $deleteExired = true): bool
    {
        if (!isset($meta['create_date']) || (!empty($meta['lifetime']) && $meta['create_date'] < date('Y-m-d H:i:s', time() - (int)$meta['lifetime']))) {
            if ($deleteExired) {
                $this->_deleteData();
            }
            return false;
        }
        return true;
    }

    /**
     * @throws \fan\core\exception\service\fatal
     */
    protected function _checkDateFormat(string $dateTime): static
    {
        if (!preg_match('/^(\d{4})\-(\d{2})\-(\d{2})\s(\d{2})\:(\d{2})\:(\d{2})$/', $dateTime, $matches)) {
            throw new fatalException($this->facade, 'Incorrect date format.');
        }
        // ToDo: Check values of number
        return $this;
    }

    // ======== The magic methods ======== \\

    // ======== Required Interface methods ======== \\

}
