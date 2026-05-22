<?php

declare(strict_types=1);

namespace fan\core\service\entity;
/**
 * Description of descriptor
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
abstract class descriptor
{
    use \fan\core\di\container_aware_trait;

    /**
     * Description object
     * @var \fan\core\service\entity\description
     */
    protected ?object $description = null;
    /**
     * Connection to database
     * @var \fan\core\service\database
     */
    protected ?object $connection = null;

    /**
     * Table Name
     * @var string
     */
    protected ?string $tableName = null;

    public function __construct(\fan\core\service\entity\description $description)
    {
        $this->description = $description;
        $this->connection  = $description->getEntity()->getConnection();
        $this->tableName   = $description->getTableName();
    }

    // ======== Static methods ======== \\
    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
    // ======== Main Interface methods ======== \\
    abstract public function getFields(): array;
    abstract public function getPrimeryKey(): string|array|null;
    abstract public function getKeys(): array;
    abstract public function getRelations(): array;
    abstract public function getEngine(): mixed;
    abstract public function getCreateTime(): mixed;
    abstract public function getTableCollation(): mixed;
    abstract public function getComment(): mixed;
    // ======== Private/Protected methods ======== \\
}
