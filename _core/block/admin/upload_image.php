<?php

declare(strict_types=1);

namespace fan\core\block\admin;
use fan\core\base\model\entity;
use fan\core\base\model\row;
use fan\core\base\model\spec_file\image\row as image_row;
use fan\core\block\base as block_base;

/**
 * Admin upload image file class for loader block
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
class upload_image extends base
{
    use upload_size_limit_provider_aware_trait;

    protected ?array $image = [];

    protected string $error = '';

    protected ?string $fileNs = null;

    public function finishConstruct(?block_base $container = null, array $containerMeta = [], bool $allowSetEmbedded = true): void
    {
        parent::finishConstruct($container, $containerMeta, $allowSetEmbedded);
        $this->image = $this->requestService()->get('image', 'F');
        if (!is_array($this->image)) {
            $this->image = null;
            return;
        }
        $uploadError = (int)$this->image['error'];
        if ($uploadError === UPLOAD_ERR_NO_FILE) {
            $this->image = null;
        } elseif ($uploadError === UPLOAD_ERR_PARTIAL) {
            $this->image = null;
            $this->error = 'File was broken!';
        } elseif ($uploadError === UPLOAD_ERR_INI_SIZE || $uploadError === UPLOAD_ERR_FORM_SIZE) {
            $this->image = null;
            $this->error = 'Incorrect file size (there is limit ' . $this->uploadSizeLimit() . ')!';
        } elseif (!$this->image['tmp_name'] || $this->image['error']) {
            $this->image = null;
        } else {
            $par = $this->imageMetadataReader()->size((string)$this->image['tmp_name']);
            if (!$par) {
                $this->image = null;
                $this->error = 'It isn\'t image!';
            }
        }
        if (!$this->error && $this->image && $this->getMeta('max_size')) {
            $w = $this->getMeta(['max_size', 'width']);
            $h = $this->getMeta(['max_size', 'height']);
            $w = is_null($w) ? null : (int)$w;
            $h = is_null($h) ? null : (int)$h;

            $color = $this->getMeta('b_color', 0XFFFFFF);
            $img = $this->imageModifyService((string)$this->image['tmp_name']);
            if ($par[0] > $w || $par[1] > $h) {
                $img->scal($w, $h, (int)$this->getMeta('mode', 1), is_array($color) ? $color : (int)$color);
            } elseif ($this->getMeta('allow_relocate', false)) {
                $img->relocate($w, $h, is_array($color) ? $color : (int)$color);
            }
            $markerMode = $this->getMeta(['water_mark', 'mode']);
            $opacity = $this->getMeta(['water_mark', 'opacity'], 10);
            if ($markerMode) {
                $img->markering((string)$markerMode, (int)$opacity);
            }
            $img->saveAndReplace(null);
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
        if (!isset($main['img_id'])) {
            $main['img_id'] = 'id_file_data';
        }
        $link = $this->getMeta('link_table');

        $mainRow = null;
        if (!$this->checkMainTableId($mainRow, $data, $main, $link)) {
            $this->setText('Incorrect main table ID');
            return;
        }

        if ($link) {
            if (!$this->checkLinkTableId($linkRow, $data, $main, $link)) {
                $this->setText('Incorrect link table ID');
                return;
            }
        } else {
            $linkRow = null;
        }

        $img = $this->getRow($this->getEttImageName(), $data['imgId'] ?? null);
        if ((string)$data['op'] === 'dl' && !empty($data['imgId'])) { // Operation: Delete
            $this->operationDeleteImage($data, $mainRow, $linkRow, $img, $main, $link);
        } elseif ((string)$data['op'] === 'ul' && $this->image) { // Operation: Upload
            $this->operationUploadImage($data, $mainRow, $linkRow, $img, $main, $link);
        } elseif ((string)$data['op'] === 'sa' && !empty($data['imgId'])) { // Operation: Set attributes
            $this->operationSetAttributes($data, $img);
        }

        $this->setJson([
            'data'    => empty($data['line']) ?
                    $this->getImageOneData($mainRow, $main, $link):
                    $this->getImageLineData($data, $link),
            'refresh' => (string)$data['op'] !== 'ad' && (string)$data['op'] !== 'sa',
            'op'      => $data['op'],
        ]);

        $this->setText('ok');
    }

    public function operationDeleteImage(array &$data, row $mainRow, ?row $linkRow, image_row $img, array $main, ?array $link): void
    {
        if ($img->checkIsLoad()) {
            if ($link) {
                $linkRow->delete();
                $linkRow->getEntity()->getConnection()->commit();
            } else {
                $mainRow->setFields([$main['img_id'] => null], true);
                $mainRow->getEntity()->getConnection()->commit();
            }
            $img->delete($this->getEttImageName(), $data['imgId']);
        }
    }

    public function operationUploadImage(array &$data, row $mainRow, ?row $linkRow, image_row $img, array $main, ?array $link): void
    {
        $req = $this->requestService();
        $img->setFormFile('image', [], $req->get('description', 'P', ''), $req->get('alt_txt', 'P', ''));
        if ($img->checkIsLoad() && empty($data['imgId'])) {
            $img->getEntity()->getConnection()->commit();
            if ($link) {
                $linkRow->setFields([$link['main_id'] => $data['id'], $link['img_id'] => $img->getId()], true);
            } else {
                $mainRow->setFields([$main['img_id'] => $img->getId()], true);
            }
        }
    }

    public function operationSetAttributes(array &$data, image_row $img): void
    {
        if ($img->checkIsLoad()) {
            $img->setFields(['alt' => $data['alt']], true);
            $img->getEntityFile()->setFields(['description' => $data['description']], true);
        } else {
            $data['op'] = null;
        }
    }

    public function checkMainTableId(mixed &$mainRow, array &$data, array $main, mixed $link): bool
    {
        $mainRow = $this->getRow((string)$main['entity'], $data['id'] ?? null);
        if (!empty($data['imgId']) && !$link) {
            $method = 'get_' . $main['img_id'];
            return (string)$mainRow->$method(null, true) === (string)$data['imgId'];
        }
        return $mainRow->checkIsLoad();
    }

    public function checkLinkTableId(mixed &$linkRow, array &$data, array $main, array $link): bool
    {
        if (empty($data['imgId'])) {
            $linkRow = $this->getRow((string)$link['entity']);
            return true;
        } else {
            $linkRow = $this->getRow((string)$link['entity'], [$link['main_id'] => $data['id'], $link['img_id'] => $data['imgId']]);
            return $linkRow->checkIsLoad();
        }
    }

    public function getImageLineData(array $data, array $link): array
    {
        $ret = [];
        $i = 0;
        $lstId = $this->getEntity((string)$link['entity'])->getRowsetByParam([$link['main_id'] => $data['id']])->toArray();
        foreach ($lstId as $v) {
            $ret[$i] = $this->getImageData($v[$link['img_id']]);
            if (isset($v['order_num'])) {
                $ret[$i]['order_num'] = $v['order_num'];
            }
            $i++;
        }
        return $ret;
    }

    public function getImageOneData(row $mainRow, array $main, mixed $link): ?array
    {
        if ($link) {
            $lstId = $this->getEntity((string)$link['entity'])->getRowsetByParam($link['main_id'])->getColumn($link['img_id']);
            return $this->getImageData($lstId[0] ?? null);
        } else {
            $method = 'get_' . $main['img_id'];
            return $this->getImageData($mainRow->$method());
        }
    }

    public function getImageData(mixed $imgId): ?array
    {
        if (!$imgId) {
            return null;
        }
        $img = $this->getRow($this->getEttImageName(), $imgId);
        if (!$img->checkIsLoad()) {
            return null;
        }
        return $img->getImageData();
    }

    /**
     * @param mixed $id Unique identifier used to locate the target item.
     */
    private function getRow(string $ettName, mixed $id = null): row
    {
        $row = $this->entityService()->get($ettName)->getNewRow();
        $con = $this->getMeta('connection');
        if ($con) {
            $row->setConnection($con);
        }
        $row->loadById($id);
        return $row;
    }

    private function getEntity(string $ettName): entity
    {
        $connection = $this->getMeta('connection');
        return empty($connection)
            ? $this->entityService()->get($ettName)
            : $this->entityService(1)->get($ettName)->setConnection($connection);
    }

    private function getEttImageName(): string
    {
        if (is_null($this->fileNs)) {
            $this->fileNs = $this->entityService()->getFileNsSuffix() . 'image';
        }
        return $this->fileNs;
    }


}
