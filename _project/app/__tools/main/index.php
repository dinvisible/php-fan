<?php

declare(strict_types=1);

namespace fan\app\__tools\main;
/**
 * Index block
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
class index extends \fan\project\block\common\simple
{
    public function init(): void
    {
        if (!role('tools_access')) {
            //$user = service('user', ['anonymous', 'tools_by_config']);
            $user = getUser('anonymous', 'tools_by_config');
            $user->setCurrent();
            if (!role('tools_access')) {
                transfer_int('~/request_password.html');
            } else {
                transfer_sham($this->tab->getCurrentURI(false, true, true, true));
            }
        }
    } // init

}
