<?php

declare(strict_types=1);

namespace fan\core\block\admin;
use fan\core\base\model\row;
use fan\core\block\base as block_base;

/**
 * Admin upload file class for loader block
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
 */
class upload_file extends base
{
    use upload_size_limit_provider_aware_trait;

    protected ?array $file = [];

    protected string $error = '';

    public function finishConstruct(?block_base $container = null, array $containerMeta = [], bool $allowSetEmbedded = true): void
    {
        parent::finishConstruct($container, $containerMeta, $allowSetEmbedded);

        $this->file = $this->requestService()->get('file', 'F');
        if (!is_array($this->file)) {
            $this->file = null;
            return;
        }
        $uploadError = (int)$this->file['error'];
        if ($uploadError === UPLOAD_ERR_NO_FILE) {
            $this->file = null;
        } elseif ($uploadError === UPLOAD_ERR_PARTIAL) {
            $this->file = null;
            $this->error = 'File was broken!';
        } elseif ($uploadError === UPLOAD_ERR_INI_SIZE || $uploadError === UPLOAD_ERR_FORM_SIZE) {
            $this->file = null;
            $this->error = 'Incorrect file size (there is limit ' . $this->uploadSizeLimit() . ')!';
        } elseif (!$this->file['tmp_name'] || $this->file['error']) {
            $this->file = null;
        }
    }

    public function init(): void
    {
        $this->roleService()->setSessionRoles('admin', $this->getMeta('login_timeout'));

        if ($this->error) {
            $this->setText($this->error);
            return;
        }

        $data = $this->getData();
        $main = $this->getMeta('main_table');
        if (!isset($main['file_id'])) {
            $main['file_id'] = 'id_file_data';
        }
        $link = $this->getMeta('link_table');

        if (!$this->checkMainTableId($mainRow, $data, $main, $link)) {
            $this->setText('Incorrect main table ID');
            return;
        }

        if ($link) {
            if (!$this->checkLinkTableId($linkRow, $data, $main, $link)) {
                $this->setText('Incorrect link table ID');
                return;
            }
        }

        $entityService = $this->entityService();
        $file = $entityService
            ->get((string)$entityService->getFileNsSuffix() . 'file_data')
            ->getRowById($data['fileId'] ?? null);
        if ((string)$data['op'] === 'dl' && !empty($data['fileId'])) {
            if ($file->checkIsLoad()) {
                if ($link) {
                    $linkRow->delete();
                    $linkRow->getEntity()->getConnection()->commit();
                } else {
                    $mainRow->setFields([$main['file_id'] => null], true);
                    $mainRow->getEntity()->getConnection()->commit();
                }
                $file->delete('file_data', $data['fileId']);
            }
        } elseif ((string)$data['op'] === 'ul' && $this->file) {
            $file->setFormFile('file', [], 'other', (string)$this->requestService()->get('description', 'P', ''));
            $accessType = $this->getMeta('access_type', null);
            if (!is_null($accessType)) {
                $file->setAccessType((string)$accessType);
            }
            if ($file->checkIsLoad() && empty($data['fileId'])) {
                $file->getEntity()->getConnection()->commit();
                if ($link) {
                    $linkRow->setFields([$link['main_id'] => $data['id'], $link['file_id'] => $file->getId()], true);
                } else {
                    $mainRow->setFields([$main['file_id'] => $file->getId()], true);
                }
            }
        }

        $jsonData = !empty($data['line']) ? $this->getFileLineData($data, $link) : $this->getFileOneData($mainRow, $main, $link);
        if (!$file->checkIsLoad() && $jsonData['id']) {
            $file->loadById($jsonData['id']);
        }
        $jsonData['filename'] = $file->checkIsLoad() ? $file->get_src_name() : '';
        $this->setJson(['data' => $jsonData]);

        $this->setText('ok');
    }

    public function checkMainTableId(mixed &$mainRow, array &$data, array $main, mixed $link): bool
    {
        $mainRow = $this->entityService()->get((string)$main['table_name'])->getRowById($data['id'] ?? null);
        if (!empty($data['fileId']) && !$link) {
            $method = 'get_' . $main['file_id'];
            return (string)$mainRow->$method(null, true) === (string)$data['fileId'];
        }
        return $mainRow->checkIsLoad();
    }

    public function checkLinkTableId(mixed &$linkRow, array &$data, array $main, array $link): bool
    {
        if (empty($data['fileId'])) {
            $linkRow = $this->entityService()->get((string)$link['table_name'])->getNewRow();
            return true;
        } else {
            $linkRow = $this->entityService()->get((string)$link['table_name'])->getRowById([$link['main_id'] => $data['id'], $link['file_id'] => $data['fileId']]);
            return $linkRow->checkIsLoad();
        }
    }

    public function getFileLineData(array $data, array $link): array
    {
        $ret = [];
        $lstId = $this->entityService()->get((string)$link['table_name'])->getRowsetByParam([$link['main_id'] => $data['id']])->getColumn($link['file_id']);
        foreach ($lstId as $v) {
            $ret[] = $this->getFileData($v);
        }
        return $ret;
    }

    public function getFileOneData(row $mainRow, array $main, mixed $link): ?array
    {
        if ($link) {
            $lstId = $this->entityService()->get((string)$link['table_name'])->getRowsetByParam($link['main_id'])->getColumn($link['file_id']);
            return $this->getFileData($lstId[0] ?? null);
        } else {
            $method = 'get_' . $main['file_id'];
            return $this->getFileData($mainRow->$method());
        }
    }

    public function getFileData(mixed $fileId): ?array
    {
        if (!$fileId) {
            return null;
        }
        // To Do: Get full info about file
        return ['id' => $fileId];
    }
}
