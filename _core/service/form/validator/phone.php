<?php

declare(strict_types=1);

namespace fan\core\service\form\validator;
/**
 * Phone class of validators
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
class phone extends base
{
    public function isUkrainianPhone(mixed $value): bool
    {
        $phone = preg_replace('/\D+/', '', (string)$value);
        if (strlen($phone) === 9) {
            $phone = '380' . $phone;
        } elseif (strlen($phone) === 10) {
            $phone = '38' . $phone;
        } elseif (strlen($phone) !== 12) {
            return false;
        }
        if (preg_match('/^380\d{9}$/', $phone)) {
            $phone = '+' . $phone;
            return true;
        }
       return false;
    }




}
