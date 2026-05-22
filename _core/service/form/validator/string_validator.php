<?php

declare(strict_types=1);

namespace fan\core\service\form\validator;
/**
 * String class of validators
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
class string_validator extends base
{

    public function strlen(mixed $value, array $data): bool
    {
        $length = isset($data['is_mb']) && empty($data['is_mb']) ? strlen($value) : mb_strlen($value);
        if (isset($data['min_length']) && $length < $data['min_length']) {
            return false;
        }
        if (isset($data['max_length']) && $length > $data['max_length']) {
            return false;
        }
        return true;
    }

    public function isUtf8(mixed $value, array $data): bool
    {
        if (!mb_check_encoding($value, 'UTF-8')) {
            return false;
        }
        if (isset($data['max_length']) || isset($data['min_length'])) {
            $data['is_mb'] = true;
            return $this->strlen($value, $data);
        }
        return true;
    }

    public function isAlphalogin(mixed $value, array $data): bool
    {
        return preg_match('/^[a-z0-9][a-z0-9_@\.-]*$/i', $value) > 0;
    }

    public function isAlphanumeric(mixed $value, array $data): bool
    {
        return preg_match('/^[a-z0-9][a-z0-9_-]*$/i', $value) > 0;
    }

    public function matchRegexp(mixed $value, array $data): bool
    {
        return preg_match($data['regexp'], $value) > 0;
    }

}
