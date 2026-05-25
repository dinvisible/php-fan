<?php
declare(strict_types=1);

namespace fan\core\service\cache;
use fan\core\service\cache;

/**
 * File cache engine
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
class file extends base
{
    /**
     * Path to File Data
     * @var string
     */
    private ?string $fileData = null;
    /**
     * Path to File Meta
     * @var string
     */
    private ?string $fileMeta = null;

    private ?object $fileStorage = null;

    public function __construct(
        cache $facade,
        string $type,
        string $key,
        array $config,
        ?object $errorLogger = null,
        ?object $runtime = null,
        ?callable $payloadEncoder = null,
        ?callable $payloadDecoder = null,
        ?callable $jsonPayloadChecker = null,
        ?object $fileStorage = null
    ) {
        parent::__construct(
            $facade,
            $type,
            $key,
            $config,
            $errorLogger,
            $runtime,
            $payloadEncoder,
            $payloadDecoder,
            $jsonPayloadChecker
        );
        $this->fileStorage = $fileStorage;
    }

    protected function _loadData(bool $loadMetaOnly): bool
    {
        list($fileData, $fileMeta) = $this->_getFilePath();
        if (!$this->fileStorage()->exists($fileMeta)) {
            return false;
        }

        $metaRaw        = $this->_readFile($fileMeta);
        $isLegacyMeta   = !$this->_isJsonPayload($metaRaw);
        $metaData       = $this->_decodePayload($metaRaw, 'Cache meta decode error');
        $this->metaData = is_array($metaData) ? $metaData : [];
        if (!is_array($metaData) || !$this->_checkActual($metaData) || !$this->fileStorage()->exists($fileData) || $loadMetaOnly) {
            return false;
        }

        $dataType     = $this->metaData['data_type'] ?? null;
        $isLegacyData = false;
        if ($dataType === 'null') {
            $this->data = null;
        } else {
            $data = $this->_readFile($fileData);
            if ($dataType === 'string') {
                $this->data = $data;
            } else {
                $isLegacyData = !$this->_isJsonPayload($data);
                $this->data   = $this->_decodePayload($data, 'Cache data decode error');
                if (is_null($this->data)) {
                    return false;
                }
            }
        }

        $canMigrate = !$this->_hasUnsupportedJsonValue($this->metaData)
            && !$this->_hasUnsupportedJsonValue($this->data);
        if (($isLegacyMeta || $isLegacyData) && $canMigrate) {
            $this->_saveData();
        }
        return true;
    }

    protected function _saveData(): static
    {
        list($fileData, $fileMeta) = $this->_getFilePath();
        $this->_checkWritable($fileMeta, 'meta');
        $this->_checkWritable($fileData, 'data');

        $dataType = $this->metaData['data_type'] ?? 'null';
        $this->fileStorage()->write($fileMeta, $this->_encodePayload($this->metaData), LOCK_EX);
        if ($dataType === 'string') {
            $this->fileStorage()->write($fileData, (string)$this->data, LOCK_EX);
        } elseif ($dataType !== 'null') {
            $this->fileStorage()->write($fileData, $this->_encodePayload($this->data), LOCK_EX);
        }
        return $this;
    }

    protected function _deleteData(): static
    {
        list($fileData, $fileMeta) = $this->_getFilePath();
        if ($this->fileStorage()->exists($fileMeta)) {
            $this->fileStorage()->delete($fileMeta);
        }
        if ($this->fileStorage()->exists($fileData)) {
            $this->fileStorage()->delete($fileData);
        }
        parent::_deleteData();
        return $this;
    }

    protected function _getFilePath(): array
    {
        if (empty($this->fileData) || empty($this->fileMeta)) {
            if (empty($this->config['BASE_DIR'])) {
                throw $this->createCacheFatalException('Base cache doesn\'t set for "' . $this->type . '".');
            }
            $path = rtrim($this->runtime()->parsePath((string)$this->config['BASE_DIR']), '/\\');
            if (!empty($this->extraPath)) {
                $path .= '/' . trim($this->extraPath, '/\\');
            }
            if (!$this->fileStorage()->isDirectory($path)) {
                $dirMode = empty($this->config['DIR_MODE']) ? 0777 : (int)$this->config['DIR_MODE'];
                if (!$this->fileStorage()->makeDirectory($path, $dirMode, true)) {
                    throw $this->createCacheFatalException('Can\'t create cache directory for "' . $this->type . '".');
                }
            } elseif (!$this->fileStorage()->isWritable($path)) {
                throw $this->createCacheFatalException('Cache directory for "' . $this->type . '" isn\'t writable.');
            }

            if (empty($this->config['CODE_FILE_NAME'])) {
                $fileName = $this->key;
                if (!preg_match('/^[a-z0-9\-_\(\)\!\.]+$/i', $fileName) || substr($fileName, -5) === '.meta') {
                    throw $this->createCacheFatalException('Cache key "' . $this->key . '" can\'t be used for name of cache file.');
                }
            } else {
                $fileName = md5($this->key);
            }

            $fileExt = isset($this->config['FILE_EXT']) ? (string)$this->config['FILE_EXT'] : 'cache';

            $this->fileData = $path . '/' . $fileName . (empty($fileExt) ? '' : '.' . $fileExt);
            $this->fileMeta = $path . '/' . $fileName . '.meta';
        }
        return [
            $this->fileData,
            $this->fileMeta,
        ];
    }

    protected function _checkWritable(string $filePath, string $type): void
    {
        if ($this->fileStorage()->isFile($filePath) && !$this->fileStorage()->isWritable($filePath)) {
            throw $this->createCacheFatalException('Cache ' . $type . '-file "' . $filePath . '" isn\'t writable.');
        }
    }

    protected function _readFile(string $filePath): string
    {
        $result = $this->fileStorage()->read($filePath);
        if ($result === false) {
            throw $this->createCacheFatalException('Cache file "' . $filePath . '" isn\'t readable.');
        }
        return $result;
    }

    private function fileStorage(): object
    {
        return $this->fileStorage ?? throw new \RuntimeException('Cache file storage is not configured for cache file engine.');
    }

}
