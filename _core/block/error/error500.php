<?php

declare(strict_types=1);

namespace fan\core\block\error;
use fan\core\block\base;

/**
 * Base abstract block of error 500
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
 * @version of file: 05.02.007 (31.08.2015)
 * @abstract
 */
abstract class error500 extends base
{
    public function setViewVars(string $error, string $message, string $combiMessage): void
    {
        $this->view->error    = $error;
        $this->view->message  = $message;
        $this->view->homeUri = $this->tab->getURI('~/');
        if ($this->getViewFormat() === 'loader') {
            $this->view->setJson('error',   $error);
            $this->view->setJson('message', $message);
            $this->view->setText($combiMessage);
        }
    }
}
