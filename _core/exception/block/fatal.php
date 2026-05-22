<?php

declare(strict_types=1);

namespace fan\core\exception\block;
/**
 * Exception a block fatal error
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
class fatal extends local
{
    public function __construct(\fan\core\block\base $block, string $logErrMsg, int $code = E_USER_ERROR, ?\Throwable $previous = null)
    {
        if (!headers_sent()) {
            header('HTTP/1.1 500 Internal Server Error');
        }

        parent::__construct($block, $logErrMsg, $code, $previous);

        $this->_logByService($logErrMsg, 'Block\'s exception (CLASS: ' . get_class($block) . ').');
    }

    protected function _defineDbOper(?string $dbOper = 'rollback'): ?string
    {
        return parent::_defineDbOper($dbOper);
    }
}
