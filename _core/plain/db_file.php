<?php

declare(strict_types=1);

namespace fan\core\plain;
//use fan\project\exception\plain\fatal as fatalException;
/**
 * Base access for plain files (uploaded to the server) class
 *
 * This file is part PHP-FAN (php-framework of Alexandr Nosov)
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

class db_file
{
    use \fan\core\di\container_aware_trait;

    /**
     * Handler object
     * @var \fan\core\service\plain
     */
    protected ?object $handler = null;

    /**
     * Plain config object
     * @var \fan\core\service\config\row
     */
    protected ?object $config = null;

    /**
     * Key of plain controller
     * @var string
     */
    protected ?string $key = null;

    /**
     * @var numeric - Id of Streams
     */
    protected mixed $streamId = null;

    protected ?string $filePath = null;
    /**
     * Content for show nail without saving
     * @var string
     */
    protected ?string $plainContent = null;
    protected ?string $fileType = null;

    protected mixed $id = null;

    /**
     * ContentDisposition: true - inline; false - attachment
     * @var
     */
    protected bool $position = true;

    /**
     * @var
     */
    protected ?string $app = null;

    /**
     * Database row
     * @var \fan\core\base\model\row
     */
    protected ?object $row = null;

    public function __construct(\fan\core\service\plain $handler, $key)
    {
        $this->handler = $handler;
        $this->key     = (string)$key;
    }

    // ======== Static methods ======== \\
    // ======== Main Interface methods ======== \\

    public function outputContent(): void
    {
        if (!empty($this->streamId)) {
            if (rewind($this->streamId) === false) {
                //ToDo: Save Error Message there
            } elseif (fpassthru($this->streamId) === false) {
                //ToDo: Save Error Message there
            }
        } elseif (!empty($this->filePath)) {
            readfile($this->filePath);
        } else {
            //ToDo: Save Error Message there
            echo 'Error file source';
        }
    } // outputFile

    public function getFile(): array|string
    {
        return $this->_prepare()->_init()->_getContent();
    } // getFile

    public function setConfig(\fan\core\service\config\row $config): static
    {
        if (empty($this->config)) {
            $this->config = $config;
        }
        return $this;
    } // setConfig

    public function getKey(): ?string
    {
        return $this->key;
    } // getKey

    // ======== Private/Protected methods ======== \\

    protected function _getContent(): array|string
    {
        return empty($this->plainContent) ? [$this, 'outputContent'] : $this->plainContent;
    }

    protected function _prepare(): static
    {
        $sr  = $this->containerService('request');
        $this->id = $sr->get('id', 'AGP');
        if (empty($this->id)) {
            $this->id = $sr->get(0, 'A');
        }
        if (!empty($this->id)) {
            $this->app      = (string)$sr->get('app',  'GPA');
            $this->fileType = (string)$sr->get('type', 'GPA');

            $this->handler->addHeader('disposition', $sr->get('pos', 'GPA', true));
            $this->handler->addHeader('response', 200);
        }
        return $this;
    }

    protected function _init(): static
    {
        if (!empty($this->app)) {
            $this->containerService('application')->setAppName($this->app);
        }

        $data = $this->_getFileData();
        if (!empty($data)) {
            $this->filePath = $data['filePath'];
            foreach (['contentType', 'filename', 'modified', 'length', 'legthRange', 'cacheLimit'] as $k) {
                if (!empty($data['headers'][$k])) {
                    $this->handler->addHeader($k, $data['headers'][$k]);
                }
            }
        }

        if (class_exists('\fan\core\service\database', false)) {
            \fan\project\service\database::close();
        }
        if (!empty($this->filePath) || !empty($this->plainContent)) {
            return $this;
        }

        if (!$this->handler->isError()) {
            $this->handler->setErrorMessage(msg('ERROR_REQUESTED_FILE_IS_NOT_FOUND'));
        }
        return $this;
    }

    protected function _getFileData($idIsEncrypt = null): ?array
    {
        if (empty($this->id)) {
            return null;
        }
        $cache = $this->containerService('cache', 'file_store');
        $cacheKey = (string)$this->id;
        $data  = $cache->get($cacheKey);
        if (!empty($data)) {
            if (!is_readable($data['filePath'])) {
                $cache->delete($cacheKey);
            } elseif (!empty($data) && (int)$data['fileDate'] === (int)filemtime($data['filePath']) && (int)$data['headers']['length'] === (int)filesize($data['filePath'])) {
                return $data;
            }
        }

        /* @var $row \fan\core\base\model\file_data\row */
        $row = $this->_getRow($idIsEncrypt);

        if ($row) {
            if (!$row->checkAccess()) {
                $this->handler->setErrorMessage(msg('ERROR_YOU_DO_NOT_HAVE_PERMISSION'), 403);
                return null;
            } else {
                $filePath = \bootstrap::parsePath((string)$row->getFilePath());
                if (!is_readable($filePath)) {
                    return null;
                }
                $data = [
                    'filePath' => $filePath,
                    'fileDate' => filemtime($filePath),
                    'headers' => [
                        'contentType' => $row->get_mime_type(),
                        'filename'    => $row->get_src_name(),
                        'length'      => filesize($filePath),
                        'legthRange'  => 'bytes',
                        'modified'    => strtotime($row->get_update_date()),
                    ],
                ];
                // ToDo: Save cache only if file do not need to check access
                $cache->set($cacheKey, $data, true);
                return $data;
            }
        }
        return null;
    }

    protected function _getRow($idIsEncrypt = null): ?\fan\core\base\model\file_data\row
    {
        if (is_null($this->row)) {
            $this->row = gr($this->containerService('entity')->getFileNsSuffix() . 'file_data');
            if (is_null($idIsEncrypt)) {
                $this->row->loadById($this->id, false); // !is_numeric($this->id)
                if (!$this->row->checkIsLoad()) {
                    $this->row->loadById($this->id, true);
                }
            } else {
                $this->row->loadById($this->id, $idIsEncrypt);
            }
        }
        return $this->row->checkIsLoad() && (is_null($this->fileType) || (string)$this->row->get_file_type() === (string)$this->fileType) ? $this->row : null;
    }

    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\

}
