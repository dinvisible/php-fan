<?php

declare(strict_types=1);

namespace fan\core\base\model\file_data;
use fan\project\exception\model\entity\fatal as fatalException;
/**
 * Row of file data
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
abstract class row extends \fan\core\base\model\row
{
    /**
     * @var \fan\core\model\access_type\row  entity access type
     */
    private ?object $at = null;

    protected ?string $storePath = null;

    protected ?string $infoPath = null;

    protected bool $loadInfo = true;
    protected bool $saveInfo = true;

    protected ?string $fileNs = null;

    public function __construct(\fan\core\base\model\entity $entity, array &$data = [], ?\fan\core\base\model\rowset $rowset = null)
    {
        parent::__construct($entity, $data, $rowset);
        $this->storePath = \bootstrap::parsePath((string)$entity->getConfig('file_store'));
        //$this->saveInfo  = $entity->getConfig('ALLOW_INFO_FILE', false);
        //$this->setAllowLoadInfo(@$_SERVER['HTTP_CACHE_CONTROL'] != 'no-cache' || !$entity->getConfig('ALLOW_CLEAR_INFO', false));
        $this->saveInfo = false;
        $this->loadInfo = false;
    }

    protected function getInfoPath(mixed $idVal): string
    {
        /*
        if (is_null($this->infoPath) && !empty($idVal)) {
            $path = $this->getMainFilePath($idVal);
            $this->infoPath = empty($path) || !$this->saveInfo ?
                '' :
                \bootstrap::parsePath($this->getConfig('INFO_FILE_PATH', '{TEMP}/file_data/file_info/')) . $path . '.php';
        }
         */
        $this->infoPath = '';
        return $this->infoPath;
    }

    public function setAllowLoadInfo(bool $loadInfo = true): void
    {
        $this->loadInfo = false;
        //$this->loadInfo = $loadInfo && $this->saveInfo;
    }

    public function loadById(mixed $idVal = null, bool $idIsEncrypt = false): mixed
    {
        if (empty($idVal)) {
            return null;
        }

        if ($idIsEncrypt) {
            $idVal = $this->getEntity()->getService()->getEncapsulant()->decryptId((string)$idVal);
        }
        $path = $this->getInfoPath($idVal);

        // Try to load data from InfoFile
        if ($this->loadInfo && !empty($path) && file_exists($path)) {
            $row = \fan\project\adapter\php_array_file::load($path, []);
            if (file_exists($this->getFilePath($row['id_file_data'], false, $row['src_name']))) {
                $this->setMainProperty($row);
                return true;
            }
            unlink($path);
        }

        // Load data from DB
        $ret = parent::loadById($idVal, false);

        $this->saveInfoFile($path, $ret);
        return $ret;
    }

    protected function saveInfoFile(mixed $path, mixed $addCond = true): bool
    {
        /*
        if (!empty($path)) {
            $isInfFile = file_exists($path);
            if ($addCond && $this->saveInfo) {
                $row = $this->getFields();
                if (!empty($row) && empty($row['id_file_access_type']) && empty($row['is_deleted']) && !empty($row['is_accessible'])) {
                    // Check for Save if content is renewed
                    if ($isInfFile) {
                        $rowF = include($path);
                        $comp = array_diff($row, $rowF);
                        if (empty($comp)) {
                            return true;
                        }
                    }
                    // Save new file
                    file_put_contents($path, '<?php
return ' . var_export($row, true) . ';
?>');
                    return true;
                }
            }
            if ($isInfFile) {
                unlink($path);
            }
        }
         */
        return false;
    }

    /**
     * @param ?int $id Unique identifier used to locate the target item.
     */
    public function getFilePath(?int $id = null, bool $checkAddCondition = true, string|int|float|null $srcName = ''): ?string
    {
        if ($id) {
            $checkAddCondition = false;
        } else {
            $id = $this->getId(false);
        }
        $path = $this->getMainFilePath((int)$id);
        if (!empty($path) && (!$checkAddCondition || $this->get_is_accessible() && !$this->get_is_deleted())) {
            $path = \bootstrap::parsePath((string)$this->getConfig('file_store') . $path);
            $parts = pathinfo((string)$this->get_src_name($srcName, false));
            $path .= '.' . (empty($parts['extension']) || (string)$parts['extension'] === 'php' ? $this->getConfig('file_ext') : $parts['extension']);
            return $path;
        }
        return null;
    }

    /**
     * @param int $id Unique identifier used to locate the target item.
     */
    protected function getMainFilePath(int $id): ?string
    {
        $path = null;
        if (!empty($id)) {
            $triadLen = strlen((string)$id) % 3;
            $path = str_repeat('0', ($triadLen ? 3 - $triadLen : 0)) . number_format((int)$id, 0, '.', '/');
            if ($this->getConfig('path_with_connection')) {
                $path = $this->getEntity()->getConnection()->getConnectionName() . '/' . $path;
            }
         }
        return $path;
    }

    /**
     * @throws fatalException
     */
    public function checkCreatedDir(string $filePath): string
    {
        $dir = dirname($filePath);

        // If path is link
        while (is_link($dir)) {
            $lnk = $dir;
            for ($i = 0; $i < 10; $i++) {
                $lnk = readlink($lnk);
                if (is_dir($lnk)) {
                    break 2;
                }
                if (!is_link($lnk)) {
                    throw new fatalException($this, 'Incorrect link to file: "' . $filePath . '". This "' . $lnk . '" isn\'t directory.');
                }
            }
            throw new fatalException($this, 'To many links to directory for file: "' . $filePath . '".');
        }

        // If directory already exists
        if (is_dir($dir) || is_link($dir)) {
            if (!is_writable($dir)) {
                throw new fatalException($this, 'Directory: ' . $dir . ' is not writable.');
            }
            return $dir;
        }
        // If file exists instead of directory
        if (is_file($dir)) {
            throw new fatalException($this, 'It is inpossible create directory: ' . $dir . ', because it is file there.');
        }
        // Try to create directory if it is not exist
        $this->checkCreatedDir($dir);
        if (!mkdir($dir)) {
            throw new fatalException($this, 'Can\'t create directory: ' . $dir . '.');
        }
        return $dir;
    }

    public function getContentDisposition(): bool
    {
        return true;
    }

    public function prepareOutput(mixed $contentDisposition = null): ?string
    {
        $filePath = $this->getFilePath();
        if ($filePath && file_exists($filePath)) {
            $sh = service('headers');
            /* @var $sh \fan\core\service\header */
            $sh->addHeader('contentType', $this->get_mime_type());
            $sh->addHeader('filename',    $this->get_src_name());
            $sh->addHeader('disposition', is_null($contentDisposition) ? $this->getContentDisposition() : $contentDisposition);
            $sh->addHeader('length',      filesize($filePath));
            $sh->addHeader('modified',    filemtime($filePath));
            $sh->addHeader('cacheLimit',  0);
            return $filePath;
        }
        return null;
    }

    public function setFormFile(string $formKey, array $addKeys = [], string $fileType = 'other', string $decription = ''): bool
    {
        if (!$this->getFileField('error', $formKey, $addKeys)) {
            $this->deleteCurrentFile();
            $filePath = $this->prepareUpdateFile((string)$this->getFileField('name', $formKey, $addKeys), (string)$this->getFileField('type', $formKey, $addKeys), $fileType, $decription);
            if (move_uploaded_file((string)$this->getFileField('tmp_name', $formKey, $addKeys), str_replace('\\', '/', (string)$filePath))) {
                return true;
            }
            $this->set_is_deleted(1);
            $this->save();
        }
        return false;
    }

    /**
     * @param string $url URL used as the external request target.
     */
    public function setUrlFile(string $url, string $fileType = 'other', string $decription = ''): bool
    {
        $curl = service('curl', $url);
        $data = $curl->exec();
        if (!$curl->getError() && $data) {
            $this->deleteCurrentFile();
            $contentDisposition = (string)$curl->getResponseHeaders('Content-Disposition');
            if ($contentDisposition !== '' && preg_match('/filename\s*\=\s*"?([^"]+)"?\s*$/', $contentDisposition, $match)) {
                $name = $match[1];
            } else {
                $pi = pathinfo($url);
                $name = $pi['basename'];
                if ($name !== urlencode($name)) {
                    $name = 'undefined';
                }
            }
            $filePath = $this->prepareUpdateFile($name, (string)$curl->getInfo(CURLINFO_CONTENT_TYPE), $fileType, $decription);
            if (file_put_contents($filePath, $data)) {
                return true;
            }
            $this->set_is_deleted(1);
            $this->save();
        }
        return false;
    }

    public function setLocalFile(string $srcPath, string $fileType = 'other', string $mimeType = 'application/octet-stream', string $decription = '', ?string $name = null, bool $deleteOrigin = false): bool
    {
        $srcPath = (string)$srcPath;
        if ($srcPath && file_exists($srcPath)) {
            $filePath = $this->prepareUpdateFile((string)($name ? $name : basename($srcPath)), (string)$mimeType, $fileType, $decription);
            clearstatcache();
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            clearstatcache();
            if ($deleteOrigin ? rename($srcPath, $filePath) : copy($srcPath, $filePath)) {
                return true;
            }
            $this->set_is_deleted(1);
            $this->save();
        }
        return false;
    }

    public function delete(): bool
    {
        $filePath = $this->getFilePath();
        $infoPath = $this->getInfoPath($this->getId(true));
        $deleted = parent::delete();
        if ($deleted) {
            $this->deleteCurrentFile($filePath, $infoPath);
        }
        return $deleted;
    }

    public function save(): static
    {
        parent::save();
        $path = $this->getInfoPath($this->getId());
        $this->saveInfoFile($path);
        return $this;
    }

    protected function getFileField(string $keyType, string $formKey, array $addKeys = []): mixed
    {
        $ret = $this->containerService('request')->get($formKey, 'F');
        $ret = $ret[$keyType];
        foreach ($addKeys as $k) {
            $ret = $ret[$k];
        }
        return $ret;
    }

    protected function deleteCurrentFile(?string $filePath = null, ?string $infoPath = null): void
    {
        if (!$filePath) {
            $filePath = $this->getFilePath();
            $infoPath = $this->getInfoPath($this->getId(false));
        }
        if ($filePath && file_exists($filePath)) {
            unlink($filePath);
        }
        if ($infoPath && file_exists($infoPath)) {
            unlink($infoPath);
        }
    }



    // ======== Set/get access ======== \\

    public function setAccessType(string $key, bool $save = true): void
    {
        if ($key) {
            $this->at = ge($this->_getFileNs() . 'file_access_type')->getRowByParam(['access_type' => $key]);
            if (!$this->at->checkIsLoad()) {
                throw new fatalException($this, 'Incorrect Access Type Key!');
            }
            $this->set_id_file_access_type($this->at->getId());
        } else {
            $this->set_id_file_access_type(null);
        }
        if ($save) {
            $this->save();
        }
    }

    public function setPersonalAccess(string $membType = 'owner', ?string $expireDate = null, int|float $accessQtt = -1, int|float $membId = 0): void
    {
        if (!$membId) {
            $membId = entity_member::getCurrentMember()->getId();
        }
        $pa = $this->getEntityPA($membId);
        $pa->setFields([
            'id_file_data' => $this->getId(),
            'id_member'    => $membId,
            'member_type'  => $membType,
            'expire_data'  => $expireDate,
            'access_qtt'   => $accessQtt,
        ], true);
    }

    public function removePersonalAccess(int $removeType = 1, ?int $membId = null): void
    {
        $pa = ge($this->_getFileNs() . 'file_personal_access')->getRowsetByParam(['id_file_data' => $this->getId()]);
        if ($removeType > 1 && !$membId) {
            throw new fatalException($this, 'Member Id for remove access doesn\'t set!');
        }
        foreach ($pa as $e) {
            $removeType = (int)$removeType;
            $memberId = (string)$e->get_id_member();
            if (!$removeType || $removeType === 1 && $e->get_member_type() !== 'owner' || $removeType === 2 && $memberId === (string)$membId || $removeType === 3 && $memberId !== (string)$membId) {
                $e->delete();
            }
        }
    }

    public function checkAccess(): bool
    {
        $ret = true;
        if ($this->get_id_file_access_type(false, true)) {
            if (!$this->at) {
                $this->at = gr($this->_getFileNs() . 'file_access_type', $this->get_id_file_access_type());
            }
            $rule = $this->at->get_access_rule();
            if ($rule) { // Check access by rule
                $ret     = false;
                $matches = [];
                foreach (explode(',', (string)$rule) as $s) {
                    if (preg_match('/^(?:([^\:]+)\:)?(.+)?$/', $s, $matches)) {
                        if (role($matches[2], $matches[1])) {
                            $ret = true;
                            break;
                        }
                    } else {
                        throw new fatalException($this, 'Incorret role rule "' . $rule . '"');
                    }
                }
            }
            if (!$ret) { // Check personal access
                $pa = $this->getEntityPA();
                if ($pa && $pa->checkIsLoad()) {
                    $qtt = $pa->get_access_qtt();
                    if (!$qtt || ($pa->get_expire_data() && date('Y-m-d H:i:s') > $pa->get_expire_data())) {
                        if ($pa->get_member_type() !== 'owner') {
                            $pa->delete();
                        }
                    } else {
                        $ret = true;
                        if ($qtt > 0) {
                            $pa->set_access_qtt($qtt - 1);
                        }
                    }
                }
            }
        }
        return $ret;
    }

    public function checkIsOwner(): bool
    {
        $pa = $this->getEntityPA();
        if (is_null($pa) || !$pa->checkIsLoad()) {
            return false;
        }
        return $pa->get_member_type() === 'owner';
    }

    protected function getEntityPA(mixed $membId = null): mixed
    {
        if (!$membId) {
            $member = getUser();
            if (!$member || !$member->checkIsLoad()) {
                return null;
            }
            $membId = $member->getId();
        }
        $pa = ge($this->_getFileNs() . 'file_personal_access')->getRowsetByParam([
            'id_file_data' => $this->getId(),
            'id_member'    => $membId,
        ]);
        return $pa;
    }

    protected function prepareUpdateFile(string $name, string $mimeType, string $fileType, string $decription): ?string
    {
        $this->set_src_name($name);
        $this->set_mime_type($mimeType);
        $this->set_file_type($fileType);
        $this->set_description($decription);
        $this->set_is_accessible(1);
        $this->set_is_deleted(0);

        if (!$this->checkIsLoad()) {
            $this->set_create_date(date('Y-m-d H:i:s'));
        }
        $this->set_update_date(date('Y-m-d H:i:s'));
        $this->save();

        $filePath = $this->getFilePath();
        $this->checkCreatedDir((string)$filePath);
        return $filePath;
    }

    protected function _getFileNs(): string
    {
        if (is_null($this->fileNs)) {
            $this->fileNs = (string)$this->containerService('entity')->getFileNsSuffix();
        }
        return $this->fileNs;
    }

}
