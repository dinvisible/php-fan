<?php

declare(strict_types=1);

namespace fan\core\exception\model;
use fan\core\base\model\entity;
use fan\core\exception\base;

/**
 * Exception a fatal error
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
 * @version of file: 05.02.011 (03.10.2015)
 */
class reverse extends base
{
    /**
     * @var \fan\core\base\model\entity
     */
    protected ?object $entity = null;

    public function __construct(
        entity $entity,
        string $logErrMsg,
        ?int $code = null,
        ?\Throwable $previous = null,
        ?object $exceptionDatabaseConnections = null,
        ?object $exceptionRuntimeLogger = null,
        ?object $exceptionRequestService = null,
        ?object $exceptionErrorService = null
    )
    {
        $this->entity = $entity;
        parent::__construct($logErrMsg, $code ?? E_USER_ERROR, $previous, $exceptionDatabaseConnections, $exceptionRuntimeLogger, $exceptionRequestService, $exceptionErrorService);
    }

    public function getEntity(): entity
    {
        return $this->entity;
    }

    protected function _defineDbOper(?string $dbOper = 'nothing'): ?string
    {
        return parent::_defineDbOper($dbOper);
    }

}
