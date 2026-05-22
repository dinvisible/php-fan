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
 * @version of file: 05.02.011 (03.10.2015)
 */
class form_part extends local
{
    /**
     * Parsed form block
     * @var \fan\core\block\base
     */
    protected ?object $formPart = null;

    protected ?array $errorMsg = null;

    public function __construct(\fan\core\block\base $block, array $errorMsg, int $code = E_USER_WARNING, ?\Throwable $previous = null)
    {
        $this->formPart = $block;
        $this->errorMsg = $errorMsg;
        parent::__construct($block, 'Form part error', $code, $previous);
    }

    public function getBlockName(): string
    {
        return $this->formPart->getBlockName();
    }

    public function getErrorMessages(): ?array
    {
        return $this->errorMsg;
    }

    protected function _defineDbOper(?string $dbOper = 'nothing'): ?string
    {
        return parent::_defineDbOper($dbOper);
    }

}
