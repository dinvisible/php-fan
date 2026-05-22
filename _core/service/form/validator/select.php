<?php

declare(strict_types=1);

namespace fan\core\service\form\validator;
/**
 * Common class of validators
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
class select extends base
{

    public function checkSelect(mixed $value, array $data): bool
    {
        $fieldData = $this->facade->getFieldData($data['prop_name']);
        if (is_array($fieldData)) {
            foreach ($fieldData as $v) {
                if ((string)$v['value'] === (string)$value) {
                    return true;
                }
            }
        }
        return false;
    }

    public function inArray(mixed $value, array $data): bool
    {
        if (!empty($data['value'])) {
            return in_array($value, $data['value']);
        }
        if (!empty($data['link_meta'])) {
            $arr = $this->getMeta($data['link_meta']); //ToDo: getMeta
            return is_array($arr) && in_array($value, $arr);
        }
        if (!empty($data['method'])) {
            $callBack = empty($data['class']) ? [$data['class'], $data['method']] : [$this->block, $data['method']];//ToDo: $this->block
            if (is_callable($callBack)) {
                $arr = call_user_func($callBack);
                return is_array($arr) && in_array($value, $arr);
            }
        }
        return false;
    }

}
