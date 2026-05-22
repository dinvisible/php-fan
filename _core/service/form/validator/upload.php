<?php

declare(strict_types=1);

namespace fan\core\service\form\validator;
/**
 * Upload file class of validators
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
 * @version of file: 05.02.006 (20.04.2015)
 */
class upload extends base
{
    public function uploadError(array $value, array $data): bool
    {
        $error = (int)$value['error'];
        return $error === UPLOAD_ERR_OK || $error === UPLOAD_ERR_NO_FILE;
    }

    public function uploadName(mixed $value, mixed $data): bool
    {
        $parts = explode('.', $value['name']);
        if ((!isset($data['double_ext']) || !empty($data['double_ext'])) && count($parts) > 3) {
            return false;
        }
        if ((!isset($data['empty_name']) || !empty($data['empty_name'])) && empty($parts[0])) {
            return false;
        }
        if (isset($data['allowed_ext']) && is_array($data['allowed_ext']) && (!isset($parts[1]) || !in_array($parts[1], $data['allowed_ext']))) {
            return false;
        }
        return true;
    }

    public function uploadMime(mixed $value, array $data): bool
    {
        $result = true;
        if (isset($data['allowed_mime'])) {
            $lFinfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = finfo_file($lFinfo, $value['tmp_name']);
            finfo_close($lFinfo);
            $result = in_array($mime, $data['allowed_mime']);
        }
        return $result;
    }
}
