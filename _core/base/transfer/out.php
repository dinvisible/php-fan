<?php

declare(strict_types=1);

namespace fan\core\base\transfer;
/**
 * Outer transfer
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
class out extends \fan\core\base\transfer
{
    public function __construct(string $newUrn, ?string $newQueryString = null, ?string $dbOper = null)
    {
        $this->transferType = 'out';
        if ($dbOper !== 'rollback') {
            $dbOper = 'commit';
        }
        parent::__construct($newUrn, $newQueryString, $dbOper);
    }
}
