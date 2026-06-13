<?php
declare(strict_types=1);

namespace fan\core\base\model\spec_file;
use fan\core\base\model\file_data\row as file_data_row;
use fan\core\base\model\row as model_row;

/**
 * Row of special files
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
 * @abstract
 */
abstract class row extends model_row
{
    /**
     * Entity File Data
     * @var \fan\core\base\model\file_data\row
     */
    protected ?object $entityFile = null;

    public function delete(): bool
    {
        $file = $this->getEntityFile();
        $deleted = parent::delete();
        if ($deleted) {
            $file->delete();
        }
        return $deleted;
    }

    protected function runAfterDelete(mixed $delId): void
    {
        $this->getEntityFile()->delete();
    }

    public function getEntityFile(): file_data_row
    {
        if (!$this->entityFile) {
            $ns = $this->namespaceName($this, 2);
            $entityFile = $this->getEntity()->createRelatedEntityRow('\\' . $ns . '\file_data');
            if (!$entityFile instanceof file_data_row) {
                $actual = get_class($entityFile);
                throw new \UnexpectedValueException('Spec-file row factory returned "' . $actual . '".');
            }
            $this->entityFile = $entityFile;
            $this->entityFile->getEntity()->setConnection($this->getEntity()->getConnection()->getConnectionName());
            $this->entityFile->setAllowLoadInfo(false);
            $this->entityFile->loadById($this->getId(false));
        }
        return $this->entityFile;
    }

    protected function get_is_deleted(): mixed
    {
        return $this->getEntityFile()->get_is_deleted();
    }

    public function get_src_name(): mixed
    {
        return $this->getEntityFile()->get_src_name();
    }


    // ======== Set/get access ======== \\

    public function setAccessType(string $key, bool $save = true): void
    {
        $this->getEntityFile()->setAccessType($key, $save);
        if ($save) {
            $this->save();
        }
    }

    public function setPersonalAccess(string $membType = 'owner', ?string $expireDate = null, int|float $accessQtt = -1, int|float $memrId = 0): void
    {
        $this->getEntityFile()->setPersonalAccess($membType, $expireDate, $accessQtt, $memrId);
    }

    public function removePersonalAccess(int $removeType = 1, int|float|null $membId = null): void
    {
        $this->getEntityFile()->removePersonalAccess($removeType, $membId);
    }

    public function checkAccess(): bool
    {
        return $this->getEntityFile()->checkAccess();
    }

    public function checkIsOwner(): bool
    {
        return $this->getEntityFile()->checkIsOwner();
    }
}
