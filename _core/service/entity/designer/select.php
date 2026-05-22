<?php

declare(strict_types=1);

namespace fan\core\service\entity\designer;
/**
 * Designer of SQL-request SELECT
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
class select extends \fan\core\service\entity\designer
{
    /**
     * SQL-request parts
     * @var string
     */
    protected array $queryParts = [
        'operAndFields'   => null,
        'fromTable'       => null,
        'joinTables'      => [],
        'whereCondition'  => [],
        'groupBy'         => null,
        'havingCondition' => [],
        'orderBy'         => null,
    ];


    // ======== Static methods ======== \\

    // ======== The magic methods ======== \\

    // ======== Main Interface methods ======== \\

    public function setSelectByParam(mixed $param, ?string $orderBy = null): static
    {
        $this->setMainSqlParts();
        $this->queryParts['whereCondition'] = $this->makeWhere($param, false);
        $this->queryParts['orderBy']        = $orderBy;
        $this->srcParam = $param;
        return $this;
    }

    public function setMainSqlParts(): static
    {
        $this->queryParts['operAndFields']  = 'SELECT *';
        $this->queryParts['fromTable']      = 'FROM `' . $this->getEntity()->getTableName() . '`';
        return $this;
    }

    // ======== Private/Protected methods ======== \\

}
