<?php

declare(strict_types=1);

namespace fan\core\exception\block;
/**
 * Exception a block local error. Usually catch immediate in the block
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
class local extends \fan\core\exception\base
{
    /**
     * Block's object
     * @var \fan\core\block\base
     */
    protected ?object $block = null;

    public function __construct(\fan\core\block\base $block, string $logErrMsg, int $code = E_USER_NOTICE, ?\Throwable $previous = null)
    {
        $this->block = $block;
        parent::__construct($logErrMsg, $code, $previous = null);
    }

    public function getBlock(): \fan\core\block\base
    {
        return $this->block;
    }

    protected function _defineDbOper(?string $dbOper = null): ?string
    {
        if (empty($dbOper) && method_exists($this->block, 'getExceptionDbOper')) {
            $dbOper = $this->block->getExceptionDbOper();
        }
        return parent::_defineDbOper($dbOper);
    }
}
