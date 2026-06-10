<?php

declare(strict_types=1);

namespace fan\core\exception\block;
use fan\core\block\base;

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
    public function __construct(
        base $block,
        string $logErrMsg,
        int $code = E_USER_ERROR,
        ?\Throwable $previous = null,
        ?object $exceptionDatabaseConnections = null,
        ?object $exceptionRuntimeLogger = null,
        ?object $exceptionRequestService = null,
        ?object $exceptionErrorService = null,
        ?object $exceptionHeaderWriter = null
    )
    {
        $this->setExceptionDependencies($exceptionDatabaseConnections, $exceptionRuntimeLogger, $exceptionRequestService, $exceptionErrorService, $exceptionHeaderWriter);
        $this->sendInternalServerErrorHeader();

        parent::__construct($block, $logErrMsg, $code, $previous, $exceptionDatabaseConnections, $exceptionRuntimeLogger, $exceptionRequestService, $exceptionErrorService, $exceptionHeaderWriter);

        $this->_logByService($logErrMsg, 'Block\'s exception (CLASS: ' . get_class($block) . ').');
    }

    protected function _defineDbOper(?string $dbOper = 'rollback'): ?string
    {
        return parent::_defineDbOper($dbOper);
    }
}
