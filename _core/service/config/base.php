<?php

declare(strict_types=1);

namespace fan\core\service\config;
use fan\core\service\config;

/**
 * Description of base
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
 * @version of file: 05.02.001 (10.03.2014)
 */
abstract class base
{
    /**
     * Facade of service
     * @var \fan\core\service\config
     */
    protected ?object $facade = null;
    /**
     * Source directory with configuration files.
     * @var string
     */
    protected string $sourceDir = '';
    /**
     * File extention
     * @var string
     */
    protected string $fileExtention = '';

    private ?object $fileStorage = null;

    public function setFacade(config $facade): static
    {
        if (empty($this->facade)) {
            $this->facade = $facade;
        }
        return $this;
    }

    public function setFileStorage(object $fileStorage): static
    {
        $this->fileStorage = $fileStorage;

        return $this;
    }

    public function getFilePath(string $fileName, bool $checkExist = true): ?string
    {
        $filePath = $this->sourceDir . $fileName . (empty($this->fileExtention) ? '' : '.' . $this->fileExtention);
        if ($this->fileStorage()->exists($filePath)) {
            return $filePath;
        }
        if ($checkExist) {
            throw $this->createConfigFatalException('Configuration file "' . $filePath . '" is not found!');
        }
        return null;
    }

    public function loadFile(?string $filePath): array
    {
        if ($filePath !== null && $this->fileStorage()->exists($filePath)) {
            return $this->_loadSourceData($filePath);
        }
        return [];
    }

    public function setDirPath(string $sourceDir): static
    {
        $this->sourceDir = rtrim($sourceDir, '/\\') . '/';
        return $this;
    }

    protected function _loadSourceData(string $srcFilePath): array
    {
        return [];
    }

    private function fileStorage(): object
    {
        return $this->fileStorage ?? throw new \RuntimeException('Config source file storage is not configured.');
    }

    private function createConfigFatalException(string $message, int $code = E_USER_ERROR, ?\Throwable $previous = null): \Throwable
    {
        if ($this->facade === null || !method_exists($this->facade, 'createConfigFatalException')) {
            throw new \RuntimeException('Config facade exception factory is not configured for config engine.');
        }

        $exception = $this->facade->createConfigFatalException($message, $code, $previous);
        if (!$exception instanceof \Throwable) {
            throw new \UnexpectedValueException('Config facade exception factory must return a throwable object.');
        }

        return $exception;
    }
}
