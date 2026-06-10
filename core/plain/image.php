<?php

declare(strict_types=1);

namespace fan\core\plain;

/**
 * Class of controller for show image, nail, etc
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
 * @version of file: 05.02.004 (25.12.2014)
 * @abstract
 */
class image extends db_file
{
    protected int|float|null $width = null;

    protected int|float|null $height = null;

    /**
     * Path to directory with QuickNail
     * @var string
     */
    protected ?string $nailDir = null;

    /**
     * Image type
     * @var string
     */
    protected ?string $imageType = null;

    // ======== Static methods ======== \\
    // ======== Main Interface methods ======== \\

    public function getImage(): array|string
    {
        $this->imageType = 'image';
        return $this->getFile();
    }

    public function getNail(): array|string
    {
        $this->imageType = 'nail';
        return $this->_prepareNail()->_init()->_getContent();
    }

    public function getAdmNail(): array|string
    {
        $this->imageType = 'adm_nail';
        return $this->_prepareNail()->_init()->_getContent();
    }

    // ======== Private/Protected methods ======== \\

    protected function _prepareNail(): static
    {
        $this->_prepare();

        if (!empty($this->id)) {
            list($this->width, $this->height) = $this->_getNailSize();
            if (empty($this->width) && empty($this->height)) {
                throw $this->createPlainFatalException('There isn\'t point width or height of nail.');
            } else {
                $dirMask = (string)$this->config->get('nail_dir', '{TEMP}/nail');
                $this->nailDir = $this->_getNailDir($dirMask, false);
            }
        }
        return $this;
    }

    protected function _getNailSize(): array
    {
        if ($this->imageType === 'adm_nail') {
            return [60, 60];
        }
        $sr = $this->context()->request();
        $width = $sr->get('w', 'GPA');
        $height = $sr->get('h', 'GPA');
        return [
            is_numeric($width) ? $width + 0 : null,
            is_numeric($height) ? $height + 0 : null,
        ];
    }

    protected function _getFileData($idIsEncrypt = null): ?array
    {
        $fileData = $this->imageType === 'image' ? parent::_getFileData($idIsEncrypt) : $this->_getNailFileData(is_null($idIsEncrypt) ? null : (bool)$idIsEncrypt);
        if ($this->imageType === 'adm_nail' && empty($fileData)) {
            $fileData = $this->_getStubFileData();
        }
        return $fileData;
    }

    protected function _getNailFileData(?bool $idIsEncrypt = null): ?array
    {
        $mainData = parent::_getFileData($idIsEncrypt);

        $isSize = !empty($this->width) || !empty($this->height);
        if ($isSize && !empty($this->nailDir)) {
            list($cache, $cacheKey, $data) = $this->_getCacheData($mainData);
            if (!empty($data) && $this->context()->fileStorage()->isFile($data['filePath'])) {
                $this->plainContent = $data['content'];
                return $data;
            }
        }

        if (!empty($mainData)) {
            if ($isSize) {
                $resultData = $this->_getNailData($mainData);
                if (!empty($resultData['filePath'])) {
                    $cache->set($cacheKey, $resultData);
                }
                $this->plainContent = $resultData['content'];
            } else {
                $resultData = $mainData;
            }
            return $resultData;
        }
        if (!empty($data)) {
            $cache->delete($cacheKey);
        }
        return null;
    }

    protected function _getStubFileData(): array
    {
        $nailStub = $this->context()->parsePath((string)$this->config->get('nail_stub', '{PROJECT}/data/image/empty_nail.gif'));
        if (!empty($nailStub) && $this->context()->fileStorage()->isReadable($nailStub)) {
            $imgData = $this->context()->imageMetadataReader()->size($nailStub);
            if (!empty($imgData)) {
                $pathInfo = pathinfo($nailStub);
                return [
                    'filePath' => $nailStub,
                    'content' => null,
                    'headers' => [
                        'contentType' => $imgData['mime'],
                        'filename'    => $pathInfo['basename'],
                        'length'      => $this->context()->fileStorage()->size($nailStub),
                        'legthRange'  => 'bytes',
                        'modified'    => $this->context()->fileStorage()->modifiedTime($nailStub),
                        'cacheLimit'  => 300,
                    ]
                ];
            }
        }
        throw new \RuntimeException('Incorrect path to admin-stab file "' . $nailStub . '"');
    }

    protected function _getNailDir(string $dirMask, $isException = false): ?string
    {
        $nailDir = empty($dirMask) ? null : rtrim($this->context()->parsePath($dirMask), '/\\');

        if (!empty($nailDir)) {
            if ($this->context()->fileStorage()->isFile($nailDir)) {
                throw $this->createPlainFatalException('Incorrect path for nail. Is file there "' . $nailDir . '"');
            } elseif (!$this->context()->fileStorage()->isDirectory($nailDir)) {
                if (!$this->context()->fileStorage()->makeDirectory($nailDir, 0744, true)) {
                    if ($isException) {
                        throw $this->createPlainFatalException('Can\'t create directory "' . $nailDir . '"');
                    }
                    $nailDir = null;
                }
            } elseif (!$this->context()->fileStorage()->isWritable($nailDir)) {
                if ($isException) {
                    throw $this->createPlainFatalException('Directory "' . $nailDir . '" isn\'t writable');
                }
                $nailDir = null;
            }
        }

        return $nailDir;
    }

    protected function _getCacheData($mainData): array
    {
        $cache = $this->context()->cache('img_nail');
        /* @var $cache \fan\core\service\cache */

        $cacheKey = (string)$this->id;
        if (!empty($this->width)) {
            $cacheKey .= 'w' . $this->width;
        }
        if (!empty($this->height)) {
            $cacheKey .= 'h' . $this->height;
        }

        $data = $cache->get($cacheKey);
        if ((string)$mainData['headers']['modified'] !== (string)$data['headers']['modified']) {
            $data = null;
        }

        return [$cache, $cacheKey, $data];
    }

    protected function _getNailData(array $mainData): array
    {
        $img = $this->context()->imageModify($mainData['filePath']);
        $img->scal($this->width, $this->height);
        $imgData = $img->getImageInfo(300, empty($this->nailDir));

        if (!empty($this->nailDir)) {
            $nailPath = $this->nailDir . '/' . $this->id . '_w' . $this->width . '_h'. $this->height . '.' . $img->getType();
            $img->saveAsNew($nailPath);
        } else {
            $nailPath = null;
        }

        return [
            'filePath' => $nailPath,
            'content'  => empty($this->nailDir) ? $imgData['content'] : null,
            'headers'  => array_merge($imgData['headers'], [
                'filename' => 'nail_' . $mainData['headers']['filename'],
                'modified' => $mainData['headers']['modified'],
            ])
        ];
    }

}
