<?php
declare(strict_types=1);

namespace fan\core\service\cache\wrapper;
/**
 * Cache for save data of file class
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
 * @version of file: 05.02.007 (31.08.2015)
 */

class file_data
{
    use \fan\core\di\container_aware_trait;

    /**
     * Row ID
     * @var integer
     */
    protected int|float|null $id = null;
    /**
     * Is Encrypted ID
     * @var boolean
     */
    protected ?bool $idIsEncrypt = null;
    /**
     * Database row
     * @var \fan\core\base\model\file_data\row
     */
    protected ?object $row = null;
    /**
     * Data
     * @var array
     */
    protected ?array $data = null;
    /**
     * Cache
     * @var \fan\core\service\cache
     */
    protected ?object $cache = null;

    public function __construct(int|\fan\core\base\model\file_data\row $rowData, ?bool $idIsEncrypt = null)
    {
        if (is_integer($rowData)) {
            $this->id          = $rowData;
            $this->idIsEncrypt = is_null($idIsEncrypt) ? null : (bool)$idIsEncrypt;
        } elseif (is_object($rowData) && $rowData instanceof \fan\core\base\model\file_data\row) {
            $this->row = $rowData;
            $this->id  = $rowData->getId();
        } else {
            throw new \fan\project\exception\error500('Incorrect call of \fan\core\service\cache\wrapper\file_data');
        }
    }

    // ======== Static methods ======== \\
    // ======== Main Interface methods ======== \\

    public function getFileData(): ?array
    {
        while (empty($this->data)) {
            $this->data  = $this->_getCache()->get((string)$this->id);
            if (!empty($this->data)) { // && $this->data['fileDate'] == filemtime($this->data['filePath']) && $this->data['headers']['length'] == filesize($this->data['filePath'])
                break;
            }

            $this->reset();
        }
        return $this->data;
    }

    public function reset(): bool
    {
        $row = $this->_getRow();

        if ($row) {
            if (!$row->checkAccess()) {
                // ToDo: Additional operation there
                return false;
            } else {
                $filePath = \bootstrap::parsePath($row->getFilePath());
                $this->data = [
                    'filePath' => $filePath,
                    'fileDate' => filemtime($filePath),
                    'rowData'  => $row->toArray(),
                ];
                // ToDo: Save cache only if file do not need to check access
                $this->_getCache()->set((string)$this->id, $this->data, true);
            }
        }
        return true;
    }

    // ======== Private/Protected methods ======== \\

    protected function _getRow(): ?\fan\core\base\model\file_data\row
    {
        if (is_null($this->row)) {
            $this->row = gr($this->containerService('entity')->getFileNsSuffix() . 'file_data');
            if (is_null($this->idIsEncrypt)) {
                $this->row->loadById($this->id, false); // !is_numeric($this->id)
                if (!$this->row->checkIsLoad()) {
                    $this->row->loadById($this->id, true);
                }
            } else {
                $this->row->loadById($this->id, $this->idIsEncrypt);
            }
        }
        return $this->row->checkIsLoad() ? $this->row : null;
    }

    protected function _getCache(): \fan\core\service\cache
    {
        if (is_null($this->cache)) {
            $this->cache = $this->containerService('cache', 'file_store');
        }
        return $this->cache;
    }

    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\

}
