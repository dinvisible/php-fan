<?php

declare(strict_types=1);

namespace fan\core\plain;
use fan\core\base\model\file_data\row as file_data_row;
use fan\core\service\config\row;
use fan\core\service\plain;

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

    protected ?object $context = null;

    public function __construct(plain $handler, $key, ?object $context = null)
    {
        $this->handler = $handler;
        $this->key     = (string)$key;
        $this->context = $context;
    }

    // ======== Static methods ======== \\
    // ======== Main Interface methods ======== \\

    public function outputContent(): void
    {
        if (!empty($this->streamId)) {
            if ($this->context()->fileStorage()->rewindStream($this->streamId) === false) {
                //ToDo: Save Error Message there
            } elseif ($this->context()->fileStorage()->passThroughStream($this->streamId) === false) {
                //ToDo: Save Error Message there
            }
        } elseif (!empty($this->filePath)) {
            $this->context()->fileStorage()->outputFile($this->filePath);
        } else {
            //ToDo: Save Error Message there
            echo 'Error file source';
        }
    } // outputFile

    public function getFile(): array|string
    {
        return $this->_prepare()->_init()->_getContent();
    } // getFile

    public function setConfig(row $config): static
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
        $sr  = $this->context()->request();
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
            $this->context()->setApplicationName($this->app);
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

        $this->context()->closeDatabaseConnections();
        if (!empty($this->filePath) || !empty($this->plainContent)) {
            return $this;
        }

        if (!$this->handler->isError()) {
            $this->handler->setErrorMessage($this->context()->message('ERROR_REQUESTED_FILE_IS_NOT_FOUND'));
        }
        return $this;
    }

    protected function _getFileData($idIsEncrypt = null): ?array
    {
        if (empty($this->id)) {
            return null;
        }
        $cache = $this->context()->cache('file_store');
        $cacheKey = (string)$this->id;
        $data  = $cache->get($cacheKey);
        if (!empty($data)) {
            if (!$this->context()->fileStorage()->isReadable($data['filePath'])) {
                $cache->delete($cacheKey);
            } elseif ((int)$data['fileDate'] === (int)$this->context()->fileStorage()->modifiedTime($data['filePath']) && (int)$data['headers']['length'] === (int)$this->context()->fileStorage()->size($data['filePath'])) {
                return $data;
            }
        }

        /* @var $row \fan\core\base\model\file_data\row */
        $row = $this->_getRow($idIsEncrypt);

        if ($row) {
            if (!$row->checkAccess()) {
                $this->handler->setErrorMessage($this->context()->message('ERROR_YOU_DO_NOT_HAVE_PERMISSION'), 403);
                return null;
            } else {
                $filePath = $this->context()->parsePath((string)$row->getFilePath());
                if (!$this->context()->fileStorage()->isReadable($filePath)) {
                    return null;
                }
                $data = [
                    'filePath' => $filePath,
                    'fileDate' => $this->context()->fileStorage()->modifiedTime($filePath),
                    'headers' => [
                        'contentType' => $row->get_mime_type(),
                        'filename'    => $row->get_src_name(),
                        'length'      => $this->context()->fileStorage()->size($filePath),
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

    protected function _getRow($idIsEncrypt = null): ?file_data_row
    {
        if (is_null($this->row)) {
            $this->row = $this->context()->fileDataRow();
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

    protected function context(): object
    {
        if ($this->context !== null) {
            return $this->context;
        }

        throw new \RuntimeException('Plain file context is not configured for db_file controller.');
    }

    protected function createPlainFatalException(string $message, int $code = E_USER_ERROR, ?\Throwable $previous = null): \Throwable
    {
        if (!method_exists($this->context(), 'createPlainFatalException')) {
            throw new \RuntimeException('Plain fatal exception factory is not configured for db_file controller.');
        }

        $exception = $this->context()->createPlainFatalException($this, $message, $code, $previous);
        if (!$exception instanceof \Throwable) {
            throw new \UnexpectedValueException('Plain fatal exception factory must return a throwable object.');
        }

        return $exception;
    }

    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\

}
