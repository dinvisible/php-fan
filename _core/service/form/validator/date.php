<?php

declare(strict_types=1);

namespace fan\core\service\form\validator;
/**
 * Date class of validators
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
class date extends base
{

    public function isDate(mixed $value, array $data): bool
    {
        $value = str_replace(',', '.', $value);

        $dateService = \fan\project\service\date::instance((string)$value);
        /* @var $dateService \fan\core\service\date */
        if (!$dateService->isValid()) {
            return false;
        }

        $date = $dateService->get('mysql');
        return (!isset($data['min_value']) || $date >= $data['min_value']) && (!isset($data['max_value']) || $date <= $data['max_value']);
    }

}
