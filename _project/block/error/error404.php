<?php

declare(strict_types=1);

namespace fan\project\block\error;
use fan\core\block\error\error404 as error_error404;

/**
 * Block for show error 404
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
 */
class error404 extends error_error404
{
    public function init(): void
    {
        $this->view->homeUri = $this->tab->getURI('~/');
    }
}
