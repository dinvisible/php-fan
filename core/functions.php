<?php

declare(strict_types=1);

/**
 * Special PHP-FAN functions
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
 * @version of file: 05.02.009 (23.09.2015)
 */
function get_class_alt(mixed $object): ?string
{
    return is_object($object) ? get_class($object) : null;
}

function get_class_name(string|object $object): ?string
{
    if (is_object($object)) {
        $object = get_class($object);
    } else if (!is_string($object)) {
        return null;
    }
    $ret = explode('\\', $object);
    return end($ret);
}

function get_ns_name(string|object $object, int $depth = 1): ?string
{
    if (is_object($object)) {
        $name = get_class($object);
    } elseif (is_string($object)) {
        $name = $object;
    } else {
        return null;
    }
    if ($depth < 0 || $depth > 40) {
        return null;
    }

    for ($i = 0; $i < $depth; $i++) {
        $pos = strrpos($name, '\\');
        $name = $pos > 0 ? substr($name, 0, $pos) : '';
    }
    return $name;
}

function is_array_alt(mixed $arr): bool
{
    return is_array($arr) || is_object($arr) && $arr instanceof \ArrayAccess;
}

function explode_alt(string $delimiter, string $string, int $size): array
{
    $result = explode($delimiter, $string, $size);
    $cnt    = count($result);
    return $cnt < $size ? array_merge($result, array_fill($cnt, $size - $cnt, null)) : $result;
}

function array_merge_recursive_alt(mixed $arrFirst): mixed
{
    if (!is_array_alt($arrFirst)) {
        if (is_null($arrFirst)) {
            $arrFirst = [];
        } else {
            $arrFirst = [$arrFirst];
        }
    }
    $numArgs = func_num_args();
    $argList = func_get_args();
    for ($i = 1; $i < $numArgs; $i++) {
        if (!is_null($argList[$i])) {
            $arrNext = is_array_alt($argList[$i]) ? $argList[$i] : [$argList[$i]];
            foreach ($arrNext as $k => $v) {
                $arrFirst[$k] = isset($arrFirst[$k]) && (is_array_alt($arrFirst[$k]) || is_array_alt($v)) ?
                    array_merge_recursive_alt($arrFirst[$k], $v) :
                    $v;
            }
        }
    }
    return $arrFirst;
}

/**
 * @param mixed $default Fallback value returned when no explicit value is available.
 */
function array_val(array|\ArrayAccess $arr, mixed $key, mixed $default = null): mixed
{
    if (is_null($key)) {
        return $default;
    }
    if (is_array($arr) || is_object($arr) && $arr instanceof \ArrayAccess) {
        if (is_array($key)) {
            if (count($key) < 1) {
                return $default;
            }
            $firstKey = array_shift($key);
            if (count($key) > 0) {
                return isset($arr[$firstKey]) ? array_val($arr[$firstKey], $key, $default) : $default;
            }
            $key = $firstKey;
        }
        return isset($arr[$key]) ? $arr[$key] : $default;
    }
    if (!is_null($arr)) {
        throw new \InvalidArgumentException('Requested source is not Array');
    }
    return $default;
}

function &array_get_element(&$source, string|array $key, mixed $make = null): mixed
{
    /**
     * Anonymous function for check data
     * @param mixed $destination - array | instance of \ArrayAccess
     * @param mixed $key - array | scalar
     * @param boolean $make - Make new element of array if it isn't exists
     * @param boolean $save - save source value (into new array) if it isn't array
     * @return boolean
     */
    $checker = function (&$destination, $key, $make, $save): bool
    {
        $isArray = is_array_alt($destination);
        if ((!$isArray || !isset($destination[$key])) && empty($make)) {
            return false; // Element not found and can't be made
        }

        if (!$isArray) { // Conv to array
            $destination = !$save || is_null($destination) ? [] : [$destination];
        }

        if (!isset($destination[$key])) { // Make element if it is not set
            $destination[$key] = null;
        }
        return true;
    };

    $null = null; // Return if element not found

    if (is_object($source) && is_null($make)) {
        $make = false;
    }

    // If key as array
    if (is_array($key)) {
        $makeInArray = is_null($make) || !empty($make);
        $useLink     = !is_object($source) || $makeInArray; // Do not use link for object, because "Magic methods" conflict there
        if ($useLink) {
            $destination =& $source;
        } else {
            $destination = $source;
        }
        for ($i = 0; isset($key[$i]); $i++) {
            if (!$checker($destination, $key[$i], $makeInArray, is_null($make))) {
                return $null;
            }
            if ($useLink) {
                $destination =& $destination[$key[$i]];
            } else {
                $destination = $destination[$key[$i]];
            }
        }
        return $destination;
    }

    // Else If key as string
    if (!$checker($source, $key, $make, true)) {
        return $null;
    }
    return $source[$key];
}

function adduceToArray(mixed $src): array
{
    if (!empty($src)) {
        switch (gettype($src)) {
        case 'object':
            return method_exists($src, 'toArray') ? $src->toArray() : (array)$src;
        case 'array':
            return $src;
        case 'integer':
        case 'double':
        case 'string':
            return [$src];
        }
    }
    return [];
}

function increaseNum(int|float $number, int|float $qtt = 2, bool $roundIt = true): int|float
{
    $tmp = $number * pow(10, $qtt);
    return $roundIt ? round($tmp) : $tmp;
}

function decreaseNum(int|float $number, int|float $qtt = 2): float
{
    return round($number / pow(10, $qtt), $qtt);
}
