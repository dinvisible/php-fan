<?php
declare(strict_types=1);

namespace fan\core\service\cache;
use fan\project\exception\service\fatal as fatalException;
/**
 * ADOdb wrapper for template engine
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

    protected function _loadData(bool $loadMetaOnly): bool
    {
        list($fileData, $fileMeta) = $this->_getFilePath();
        if (!file_exists($fileMeta)) {
            return false;
        }

        $metaRaw        = $this->_readFile($fileMeta);
        $isLegacyMeta   = !$this->_isJsonPayload($metaRaw);
        $metaData       = $this->_decodePayload($metaRaw, 'Cache meta decode error');
        $this->metaData = is_array($metaData) ? $metaData : [];
        if (!is_array($metaData) || !$this->_checkActual($metaData) || !file_exists($fileData) || $loadMetaOnly) {
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
        file_put_contents($fileMeta, $this->_encodePayload($this->metaData), LOCK_EX);
        if ($dataType === 'string') {
            file_put_contents($fileData, $this->data, LOCK_EX);
        } elseif ($dataType !== 'null') {
            file_put_contents($fileData, $this->_encodePayload($this->data), LOCK_EX);
        }
        return $this;
    }

    protected function _deleteData(): static
    {
        list($fileData, $fileMeta) = $this->_getFilePath();
        if (file_exists($fileMeta)) {
            unlink($fileMeta);
        }
        if (file_exists($fileData)) {
            unlink($fileData);
        }
        parent::_deleteData();
        return $this;
    }

    protected function _getFilePath(): array
    {
        if (empty($this->fileData) || empty($this->fileMeta)) {
            if (empty($this->config['BASE_DIR'])) {
                throw new fatalException($this->facade, 'Base cache doesn\'t set for "' . $this->type . '".');
            }
            $path = rtrim(\bootstrap::parsePath((string)$this->config['BASE_DIR']), '/\\');
            if (!empty($this->extraPath)) {
                $path .= '/' . trim($this->extraPath, '/\\');
            }
            if (!is_dir($path)) {
                $dirMode = empty($this->config['DIR_MODE']) ? 0777 : (int)$this->config['DIR_MODE'];
                if (!mkdir($path, $dirMode, true)) {
                    throw new fatalException($this->facade, 'Can\'t create cache directory for "' . $this->type . '".');
                }
            } elseif (!is_writable($path)) {
                throw new fatalException($this->facade, 'Cache directory for "' . $this->type . '" isn\'t writable.');
            }

            if (empty($this->config['CODE_FILE_NAME'])) {
                $fileName = $this->key;
                if (!preg_match('/^[a-z0-9\-_\(\)\!\.]+$/i', $fileName) || substr($fileName, -5) === '.meta') {
                    throw new fatalException($this->facade, 'Cache key "' . $this->key . '" can\'t be used for name of cache file.');
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
        if (is_file($filePath) && !is_writable($filePath)) {
            throw new fatalException($this->facade, 'Cache ' . $type . '-file "' . $filePath . '" isn\'t writable.');
        }
    }

    protected function _readFile(string $filePath): string
    {
        $result = file_get_contents($filePath);
        if ($result === false) {
            throw new fatalException($this->facade, 'Cache file "' . $filePath . '" isn\'t readable.');
        }
        return $result;
    }

}
