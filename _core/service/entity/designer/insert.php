<?php

declare(strict_types=1);

namespace fan\core\service\entity\designer;
/**
 * Designer of SQL-request INSERT
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
class insert extends \fan\core\service\entity\designer
{
    /**
     * SQL-request parts
     * @var string
     */
    protected array $queryParts = [
        'insertTable' => null,
        'setData'     => [],
    ];


    // ======== Static methods ======== \\

    // ======== The magic methods ======== \\

    // ======== Main Interface methods ======== \\

    public function setInsertByParam(array $param): static
    {
        $this->queryParts = [
            'insertTable' => 'INSERT INTO `' . $this->getEntity()->getTableName() . '` SET ',
            'setData'     => $this->_makeSetupPart($param),
        ];
        $this->srcParam = $param;
        return $this;
    }

    // ======== Private/Protected methods ======== \\

}
