<?php
declare(strict_types=1);

namespace fan\core\service\cache\wrapper;
use fan\core\base\model\file_data\row;
use fan\core\service\cache;

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

    private ?\Closure $cacheFactory = null;

    private ?\Closure $entityFactory = null;

    private ?object $runtime = null;

    private ?object $fileMetadata = null;

    public function __construct(
        int|row $rowData,
        ?bool $idIsEncrypt = null,
        ?callable $cacheFactory = null,
        ?callable $entityFactory = null,
        ?object $runtime = null,
        ?object $fileMetadata = null
    )
    {
        $this->cacheFactory = \Closure::fromCallable(
            $cacheFactory ?? static function (string $type): cache {
                throw new \RuntimeException('Cache dependency is not configured for file data cache wrapper.');
            }
        );
        $this->entityFactory = \Closure::fromCallable(
            $entityFactory ?? static function (): object {
                throw new \RuntimeException('Entity dependency is not configured for file data cache wrapper.');
            }
        );
        $this->runtime = $runtime;
        $this->fileMetadata = $fileMetadata;

        if (is_integer($rowData)) {
            $this->id          = $rowData;
            $this->idIsEncrypt = is_null($idIsEncrypt) ? null : (bool)$idIsEncrypt;
        } else {
            $this->row = $rowData;
            $this->id  = $rowData->getId();
        }
    }

    // ======== Static methods ======== \\
    // ======== Main Interface methods ======== \\

    public function getFileData(): ?array
    {
        while (empty($this->data)) {
            $this->data  = $this->_getCache()->get((string)$this->id);
            if (!empty($this->data)) {
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
                $filePath = $this->runtime()->parsePath((string)$row->getFilePath());
                $this->data = [
                    'filePath' => $filePath,
                    'fileDate' => $this->fileMetadata()->modifiedTime($filePath),
                    'rowData'  => $row->toArray(),
                ];
                // ToDo: Save cache only if file do not need to check access
                $this->_getCache()->set((string)$this->id, $this->data, true);
            }
        }
        return true;
    }

    // ======== Private/Protected methods ======== \\

    protected function _getRow(): ?row
    {
        if (is_null($this->row)) {
            $entityService = $this->entity();
            $this->row = $entityService
                ->get($entityService->getFileNsSuffix() . 'file_data')
                ->getNewRow();
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

    protected function _getCache(): cache
    {
        if (is_null($this->cache)) {
            $this->cache = $this->cache('file_store');
        }
        return $this->cache;
    }

    private function cache(string $type): cache
    {
        if (!isset($this->cacheFactory)) {
            $this->cacheFactory = \Closure::fromCallable(
                static function (string $type): cache {
                    throw new \RuntimeException('Cache dependency is not configured for file data cache wrapper.');
                }
            );
        }

        $cache = ($this->cacheFactory)($type);
        if (!$cache instanceof cache) {
            throw new \UnexpectedValueException('Cache dependency must be an instance of ' . cache::class . '.');
        }

        return $cache;
    }

    private function entity(): object
    {
        if (!isset($this->entityFactory)) {
            $this->entityFactory = \Closure::fromCallable(
                static function (): object {
                    throw new \RuntimeException('Entity dependency is not configured for file data cache wrapper.');
                }
            );
        }

        $entity = ($this->entityFactory)();
        if (!is_object($entity)) {
            throw new \UnexpectedValueException('Entity dependency must be an object.');
        }

        return $entity;
    }

    private function runtime(): object
    {
        if ($this->runtime === null) {
            throw new \RuntimeException('Bootstrap runtime dependency is not configured for file data cache wrapper.');
        }

        return $this->runtime;
    }

    private function fileMetadata(): object
    {
        if ($this->fileMetadata === null) {
            throw new \RuntimeException('File metadata dependency is not configured for file data cache wrapper.');
        }

        return $this->fileMetadata;
    }

    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\

}
