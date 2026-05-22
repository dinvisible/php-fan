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
if (!defined('MYSQL_ASSOC')) {
    define('MYSQL_ASSOC', defined('MYSQLI_ASSOC') ? MYSQLI_ASSOC : 1);
}
if (!defined('MYSQL_NUM')) {
    define('MYSQL_NUM', defined('MYSQLI_NUM') ? MYSQLI_NUM : 2);
}
if (!defined('MYSQL_BOTH')) {
    define('MYSQL_BOTH', defined('MYSQLI_BOTH') ? MYSQLI_BOTH : 3);
}

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

function getCurBlockInfo(): array
{
    if (!class_exists('\fan\project\service\tab', false)) {
        return [NULL, NULL];
    }
    $tab   = service_container()->get('tab');
    $block = $tab->getCurrentBlock();
    if ($block) {
        $loader     = bootstrap::getLoader();
        $reflection = new \ReflectionClass($block);
        $path       = $reflection->getFileName();
        $realPath   = $loader->getRealPath($path);
        if ($realPath) {
            $path = str_replace($loader->project, '{PROJECT}', $realPath);
        }
    } else {
        $path = NULL;
    }
    return [$tab->getTabStage(), $path];
}

function service(string $serviceName, mixed $arguments = []): mixed
{
    $arguments = empty($arguments) ? [] : (is_array($arguments) ? $arguments : [$arguments]);
    $container = service_container();
    if ($container->has($serviceName)) {
        return $container->get($serviceName, ...$arguments);
    }

    $factory = service_factory();
    if (!$factory->has($serviceName)) {
        return null;
    }

    return $factory->create($serviceName, $arguments);
}

function service_container(?\fan\core\di\container_interface $container = null): \fan\core\di\container_interface
{
    if ($container !== null) {
        \fan\core\di\container_registry::set($container);
    }

    return \fan\core\di\container_registry::get();
}

function service_factory(?\fan\core\di\service_factory_interface $factory = null): \fan\core\di\service_factory_interface
{
    if ($factory !== null) {
        \fan\core\di\container_registry::setFactory($factory);
    }

    return \fan\core\di\container_registry::getFactory();
}

function reset_service_container(): void
{
    \fan\core\di\container_registry::reset();
}

function handleError(int|float $errNo, string $errMsg, ?string $fileName = null, int|float|null $lineNum = null, ?array $errConText = null): ?bool
{
    if (!error_reporting()) {
        return null;
    }
    if ($errNo === E_DEPRECATED || $errNo === E_USER_DEPRECATED) {
        return true;
    }
    if (class_exists('\fan\project\service\error', false) || !\bootstrap::getLoader()->isLoading()) {
        service_container()->get('error')->handleError($errNo, $errMsg, $fileName, $lineNum, $errConText);
    } else {
        \bootstrap::handleError($errNo, $errMsg, $fileName, $lineNum, $errConText);
    }
    return null;
}

function getUser(mixed $identifyer = null, ?string $userSpace = null): mixed
{
    return empty($identifyer) ?
            \fan\project\service\user::getCurrent($userSpace) :
            \fan\project\service\user::instance($identifyer, $userSpace);
}

function ge(string $entityName, $collection = 0, array $param = []): \fan\core\base\model\entity
{
    return service_container()->get('entity', $collection)->get($entityName, $param);
}
function gr(string $entityName, mixed $rowId = null, bool $idIsEncrypt = false, array $param = []): \fan\core\base\model\row
{
    return service_container()->get('entity')->get($entityName, $param)->getRowById($rowId, $idIsEncrypt);
}
function se(string $entityName): \fan\core\base\model\entity
{
    trigger_error('Function "se" is deprecated. Use ge() instead.', E_USER_DEPRECATED);
    return ge($entityName);
}
function le(string $entityName, mixed $rowId = null, $idIsEncrypt = false): \fan\core\base\model\row
{
    trigger_error('Function "le" is deprecated. Use gr() instead.', E_USER_DEPRECATED);
    return gr($entityName, $rowId, (bool)$idIsEncrypt);
}

function dms(string $key, mixed $defaultValue = null): mixed
{
    $scalarValue = service_container()->get('entity')->getDynamicMetaScalar($key);
    return empty($scalarValue) ? $defaultValue : $scalarValue;
}

function dma(string $key, $defaultValue = []): mixed
{
    $result = service_container()->get('entity')->getDynamicMetaArray($key);
    return empty($result) ? $defaultValue : $result;
}

function role(string $roleCondition): bool
{
    return service_container()->get('role')->check($roleCondition);
}

function transfer_out(string $newUrl, ?string $newQueryString = null, ?string $dbOper = null): never
{
    throw new \fan\project\base\transfer\out($newUrl, $newQueryString, $dbOper);
}

function transfer_int(string $newUrl, ?string $newQueryString = null, ?string $dbOper = null): never
{
    throw new \fan\project\base\transfer\transfer_int($newUrl, $newQueryString, $dbOper);
}

function transfer_sham(string $newUrl, ?string $newQueryString = null, ?string $dbOper = null): never
{
    throw new \fan\project\base\transfer\sham($newUrl, $newQueryString, $dbOper);
}

function dateL2M(string $date, string $format = 'euro'): string
{
    return service('date', [$date, $format])->get('mysql');
}

function dateM2L(string $date, string $format = 'euro'): string
{
    return service('date', [$date, 'mysql'])->get($format);
}

function msg(): mixed
{
    static $lng = null, $msg = [];
    $arg = func_get_args();

    if (empty($arg[0])) {
        throw new \InvalidArgumentException('Error! Call "msg" without arguments.');
    }

    if (count($arg) > 1) {
        return service('translation')->getCombiMessage($arg);
    }

    $sl = service_container()->get('locale');
    $st = service('translation');
    if ((string)$sl->getLanguage() === (string)$lng && isset($msg[$arg[0]])) {
        return $msg[$arg[0]];
    }

    if ($lng !== false) {
        if ($st->getConfig('ALLOW_QUICK_MSG', true)) {
            $lng = $sl->getLanguage();
            $msg = $st->getMessageArr($lng);
        } else {
            $lng = false;
        }
    }
    return $st->getMessage($arg[0]);
}

function msgAlt(): mixed
{
    $arg = func_get_args();
    return count($arg) > 1 ? service('translation')->getCombiMessageAlt($arg) : $arg[0];
}

function d(mixed $data, string $title = 'Custom dump', string $note = '', int|float|null $dataDepth = null, bool $isTrace = true): void
{
    service('log')->logData('dump', $data, $title, $note, $dataDepth, $isTrace);
}

function l(string $message, string $title = 'Custom message', string $note = '', string $type = 'custom'): void
{
    service('log')->logMessage($type, $message, $title, $note);
}
