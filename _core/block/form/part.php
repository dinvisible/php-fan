<?php

declare(strict_types=1);

namespace fan\core\block\form;
/**
 * Part of form block abstract
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
 * @version of file: 05.02.005 (12.02.2015)
 * @abstract
 */
abstract class part extends usual
{
    // ------------ Functions for other parts ------------ \\
    protected function partInit(?\fan\core\block\form\parser $mainFormBlock = null): void
    {
    }

    protected function _parseForm(mixed $parceEmpty = true, mixed $parsingCondition = false, mixed $allowTransfer = false, bool $showWarning = true): bool
    {
        if ($showWarning) {
            throw new \LogicException('Do not run method "_parseForm" in part of form. It was runned in block "' . $this->blockName . '".');
        } else {
            parent::_parseForm((bool)$parceEmpty, (bool)$parsingCondition, (bool)$allowTransfer);
        }
        return $this->getForm()->isError();
    }
}
