<?php

declare(strict_types=1);

namespace fan\core\service\form\validator;
/**
 * Uri class of validators
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
class uri extends base
{

    public function isEmail(mixed $value, array $data): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function isUri(mixed $value, array $data): bool
    {
        if ($data['is_path']) {
            $result = filter_var($value, FILTER_FLAG_PATH_REQUIRED);
        } elseif ($data['is_query']) {
            $result = filter_var($value, FILTER_FLAG_QUERY_REQUIRED);
        } else {
            $result = filter_var($value, FILTER_VALIDATE_URL);
        }
        return $result !== false;
    }

}
