<?php

declare(strict_types=1);

namespace fan\core\block\admin;
/**
 * Admin upload flash-file class for loader block
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
class upload_flash extends base
{

    protected ?array $flash = [];

    protected string $error = '';

    public function finishConstruct(?\fan\core\block\base $container = null, array $containerMeta = [], bool $allowSetEmbedded = true): void
    {
        parent::finishConstruct($container, $containerMeta, $allowSetEmbedded);

        $this->flash = $this->containerService('request')->get('flash', 'F');
        if (!is_array($this->flash)) {
            $this->flash = null;
            return;
        }
        $uploadError = (int)$this->flash['error'];
        if ($uploadError === UPLOAD_ERR_NO_FILE) {
            $this->flash = null;
        } elseif ($uploadError === UPLOAD_ERR_PARTIAL) {
            $this->flash = null;
            $this->error = 'File was broken!';
        } elseif ($uploadError === UPLOAD_ERR_INI_SIZE || $uploadError === UPLOAD_ERR_FORM_SIZE) {
            $this->flash = null;
            $this->error = 'Incorrect file size (there is limit ' . ini_get('upload_max_filesize') . ')!';
        } elseif (!$this->flash['tmp_name'] || $this->flash['error']) {
            $this->flash = null;
        }
    }

    public function init(): void
    {
        $this->containerService('role')->setSessionRoles('admin', $this->getMeta('login_timeout'));

        if ($this->error) {
            $this->setText($this->error);
            return;
        }

        $data = $this->getData();
        $main = $this->getMeta('main_table');
        if (!isset($main['flash_id'])) {
            $main['flash_id'] = 'id_file_data';
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
        $flash = gr((string)$this->containerService('entity')->getFileNsSuffix() . 'flash', $data['flashId'] ?? null);
        if ((string)$data['op'] === 'dl' && !empty($data['flashId'])) {
            if ($flash->checkIsLoad()) {
                if ($link) {
                    $linkRow->delete();
                    $linkRow->getEntity()->getConnection()->commit();
                } else {
                    $mainRow->setFields([$main['flash_id'] => null], true);
                    $mainRow->getEntity()->getConnection()->commit();
                }
                $flash->delete('flash', $data['flashId']);
            }
        } elseif ((string)$data['op'] === 'ul' && $this->flash) {
            $req = $this->containerService('request');
            $flash->setFormFile('flash', [], (string)$req->get('description', 'P', ''), $req->get('width', 'P', 100), $req->get('height', 'P', 100), (string)$req->get('bgcolor', 'P', ''));
            if ($flash->checkIsLoad() && empty($data['flashId'])) {
                $flash->getEntity()->getConnection()->commit();
                if ($link) {
                    $linkRow->setFields([$link['main_id'] => $data['id'], $link['flash_id'] => $flash->getId()], true);
                } else {
                    $mainRow->setFields([$main['flash_id'] => $flash->getId()], true);
                }
            }
        }

        $jsonData = !empty($data['line']) ? $this->getFlashLineData($data, $link) : $this->getFlashOneData($mainRow, $main, $link);
        if (!$flash->checkIsLoad() && $jsonData['id']) {
            $flash->loadById($jsonData['id']);
        }
        $jsonData['filename'] = $flash->checkIsLoad() ? $flash->get_src_name() : '';
        $this->setJson(['data' => $jsonData]);


        $this->setText('ok');
    }

    public function checkMainTableId(mixed &$mainRow, array &$data, array $main, mixed $link): bool
    {
        $mainRow = gr((string)$main['table_name'], $data['id'] ?? null);
        if (!empty($data['flashId']) && !$link) {
            $method = 'get_' . $main['flash_id'];
            return (string)$mainRow->$method(null, true) === (string)$data['flashId'];
        }
        return $mainRow->checkIsLoad();
    }

    public function checkLinkTableId(mixed &$linkRow, array &$data, array $main, array $link): bool
    {
        if (empty($data['flashId'])) {
            $linkRow = gr((string)$link['table_name']);
            return true;
        } else {
            $linkRow = gr((string)$link['table_name'], [$link['main_id'] => $data['id'], $link['flash_id'] => $data['flashId']]);
            return $linkRow->checkIsLoad();
        }
    }

    public function getFlashLineData(array $data, array $link): array
    {
        $ret = [];
        $lstId = ge((string)$link['table_name'])->getRowsetByParam([$link['main_id'] => $data['id']])->getColumn($link['flash_id']);
        foreach ($lstId as $v) {
            $ret[] = $this->getFlashData($v);
        }
        return $ret;
    }

    public function getFlashOneData(\fan\core\base\model\row $mainRow, array $main, mixed $link): ?array
    {
        if ($link) {
            $lstId = ge((string)$link['table_name'])->getRowsetByParam($link['main_id'])->getColumn($link['flash_id']);
            return $this->getFlashData($lstId[0] ?? null);
        } else {
            $method = 'get_' . $main['flash_id'];
            return $this->getFlashData($mainRow->$method());
        }
    }

    public function getFlashData(mixed $flashId): ?array
    {
        if (!$flashId) {
            return null;
        }
        // To Do: Get full info about flash
        return ['id' => $flashId];
    }
}
