<?php

declare(strict_types=1);

namespace fan\core\service\form\validator;
/**
 * Number class of validators
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
class number extends base
{

    public function isInt(mixed $value, array $data): bool
    {
        if (!preg_match('/^\-?\d+$/', $value)) {
            return false;
        }
        if (isset($data['min_value']) && $value < $data['min_value']) {
            return false;
        }
        if (isset($data['max_value']) && $value > $data['max_value']) {
            return false;
        }
        return true;
    }

    public function isFloat(mixed $value, array $data): bool
    {
        $value = str_replace(',', '.', $value);
        if (!is_numeric($value)) {
            return false;
        }
        if (isset($data['min_value']) && $value < $data['min_value'] - 0.000001) {
            return false;
        }
        if (isset($data['max_value']) && $value > $data['max_value'] + 0.000001) {
            return false;
        }
        return true;
    }

    public function equalTo(mixed $value, array $data): bool
    {
        $value2 = null;
        if (!empty($data['compare_field'])) {
            $value2 = $this->facade->getFieldValue($data['compare_field']);
        }
        return (string)$value === (string)$value2;
    }

    public function notEqualTo(mixed $value, array $data): bool
    {
        $value2 = null;
        if (isset($data['compare_field'])) {
            $value2 = array_val($this->fieldValue, $data['compare_field']);
        }
        return (string)$value !== (string)$value2;
    }

    public function greaterThan(mixed $value, array $data): bool
    {
        $value2 = null;
        if (isset($data['compare_field'])) {
            $value2 = array_val($this->fieldValue, $data['compare_field']);
        }
        $dataType = (string)($data['data_type'] ?? '');
        if ($dataType === 'DATE' || $dataType === 'DATETIME') {
            $value  = \fan\project\service\date::instance((string)$value)->get('mysql');
            $value2 = \fan\project\service\date::instance((string)$value2)->get('mysql');
        }
        return $value > $value2;
    }

    public function lesserThan(mixed $value, array $data): bool
    {
        $value2 = null;
        if (isset($data['compare_field'])) {
            $value2 = array_val($this->fieldValue, $data['compare_field']);
        }
        $dataType = (string)($data['data_type'] ?? '');
        if ($dataType === 'DATE' || $dataType === 'DATETIME') {
            $value  = \fan\project\service\date::instance((string)$value)->get('mysql');
            $value2 = \fan\project\service\date::instance((string)$value2)->get('mysql');
        }
        return $value < $value2;
    }

    public function greaterOrEqualTo(mixed $value, array $data): bool
    {
        $value2 = null;
        if (isset($data['compare_field'])) {
            $value2 = array_val($this->fieldValue, $data['compare_field']);
        }
        return $value >= $value2;
    }

    public function lesserOrEqualTo(mixed $value, array $data): bool
    {
        $value2 = null;
        if (isset($data['compare_field'])) {
            $value2 = array_val($this->fieldValue, $data['compare_field']);
        }
        return $value <= $value2;
    }

}
