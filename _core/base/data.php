<?php

declare(strict_types=1);

namespace fan\core\base;
/**
 * Any types of Data (config, meta, entity, etc)
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
abstract class data implements \ArrayAccess, \Iterator, \Countable
{
    use \fan\core\di\container_aware_trait;

    /**
     * Error messages
     * @var array
     */
    protected array $errMsg = [
        0  => 'Reserved for fatal error.',
        3  => 'Incorrect type of key "{KEY_TYPE}". Call class "{CLASS}".',
        5  => 'Unknown type ({TYPE}) of setter.',
        10 => 'Unrecognised setter tryed to change data in "{CLASS}".',
        11 => 'Unrecognised setter tryed to reset data in "{CLASS}".',
        12 => 'Unrecognised setter tryed to unset data for key "{KEY}" in "{CLASS}".',
        14 => 'Set new data data inpossible for key "{KEY}" in the "{CLASS}".',
    ];

    /**
     * Saved data
     * @var array
     */
    protected array $data = [];

    /**
     * List of classes who can set/change data
     * If array is empty - any caller can set/change data
     * @var array
     */
    protected array $setter = [];

    /**
     * Flag shows there are subelements - instances of this class
     * @var boolean
     */
    protected bool $multiLevel = true;

    /**
     * Key of superior in multilevel systems (if null - this is root element)
     * @var string
     */
    protected int|string|null $key = null;

    /**
     * Superior in multilevel systems (if null - this is root element)
     * @var \fan\core\base\data
     */
    protected ?object $superior = null;

    /**
     * Flag allows Full data Rewrite
     * @var boolean
     */
    protected bool $fullRewrite = false;

    public function __construct(mixed $data = null, int|string|null $key = null, ?\fan\core\base\data $superior = null)
    {
        $this->key      = $key;
        $this->superior = $superior;
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $this->set($k, $v, true);
            }
        }
    }

    // ======== Main Interface methods ======== \\
    /**
     * @param mixed $default Fallback value returned when no explicit value is available.
     */
    public function get(mixed $key = null, mixed $default = null, bool $logError = false): mixed
    {
        if (is_scalar($key)) {
            return isset($this->data[$key]) ? $this->data[$key] : $default;
        } elseif (is_null($key)) {
            return $this;
        } elseif (!is_array($key)) {
            if ($logError) {
                $this->_logError(3, ['key_type' => gettype($key)]);
            }
            return $default;
        }
        return $this->multiLevel ? $this->_getMultilevelData($key, $default, $logError) : array_get_element($this->data, $key, false);
    }

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    public function set(mixed $key, mixed $value, bool $rewriteExisting = true, ?bool $convArray = null): static
    {
        if ($this->_checkSetter()) {
            $convArray = is_null($convArray) ? $this->multiLevel : !empty($convArray);

            if (is_scalar($key)) {
                if ($this->multiLevel && is_array($value) && isset($this->data[$key]) && $this->_isThisClass($this->data[$key])) {
                    foreach ($value as $k => $v) {
                        $this->data[$key]->set($k, $v, $rewriteExisting, $convArray);
                    }
                } elseif ($rewriteExisting || !isset($this->data[$key])) {
                    $this->data[$key] = is_array($value) && $convArray ? $this->_makeSubData($key, $value) : $value;
                } else {
                    $this->_logError(14, ['key' => $key]);
                }
            } elseif ($this->multiLevel && is_array($key)) {
                $path = $key;
                $firstKey = array_shift($path);
                if (empty($path)) {
                    $this->set($firstKey, $value, $rewriteExisting, $convArray);
                } else {
                    if (!isset($this->data[$firstKey]) || !$this->_isThisClass($this->data[$firstKey])) {
                        $this->data[$firstKey] = $this->_makeSubData($firstKey, []);
                    }

                    if (!is_object($this->data[$firstKey])) {
                        throw new \UnexpectedValueException('Element of data with key "' . $firstKey . '" has incorrect type "' . gettype($this->data[$firstKey]) . '" in class "' . get_class() . '".');
                    } elseif (!method_exists($this->data[$firstKey], 'set')) {
                        throw new \UnexpectedValueException('Element of data with key "' . $firstKey . '" is instance of class "' . get_class($this->data[$firstKey]) . '" without method "set" in container class "' . get_class() . '".');
                    } else {
                        $this->data[$firstKey]->set($path, $value, $rewriteExisting, $convArray);
                    }
                }
            } elseif (is_array($key)) {
                $data =& array_get_element($this->data, $key, true);
                $data = $value;
            } elseif (is_null($key) && 0) {
                // ToDo: $this->data = $value;
            } else {
                $this->_logError(3, ['key_type' => gettype($key)]);
            }

        } else {
            $this->_logError(10);
        }
        return $this;
    }

    public function toArray(): array
    {
        if (!$this->multiLevel) {
            return $this->data;
        }
        $ret = [];
        foreach ($this->data as $k => $v) {
            $ret[$k] = $this->_isThisClass($v) ? $v->toArray() : $v;
        }
        return $ret;
    }

    public function isFullRewrite(): bool
    {
        return $this->fullRewrite;
    }

    // ======== Private/Protected methods ======== \\

    protected function _restoreSetters(): static
    {
        //Redefine this method for restore list of Setters
        return $this;
    }

    protected function _setSetter(object|string $setter): bool
    {
        if (is_object($setter) || is_string($setter)) {
            $this->setter[] = $setter;
            return true;
        }
        $this->_logError(5, ['type' => gettype($setter)]);
        return false;
    }

    protected function _checkSetter(): bool
    {
        if (empty($this->setter)) {
            return true;
        }
        $trace = debug_backtrace();
        // Skip calling from this class
        do {
            foreach ($trace as $link) {
                // ToDo: There is possible collisie for MultiLevel data (Instances of this classes from another branches can change data there)
                if (!isset($link['object']) || ($this->multiLevel ? !$this->_isThisClass($link['object']) : $link['object'] !== $this)) {
                    break 2;
                }
            }
            return false;
        } while (false);

        // Check object or class of caller
        foreach ($this->setter as $v) {
            if (is_object($v)) {
                if (!empty($link['object']) && $link['object'] === $v) {
                    return true;
                }
            } elseif ($this->_checkSetterClass($link, $v)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param mixed $default Fallback value returned when no explicit value is available.
     */
    protected function _getMultilevelData(array $key, mixed $default, bool $logError): mixed
    {
        $path = $key;
        $firstKey = array_shift($path);
        if (empty($path)) {
            return $this->get($firstKey, $default, $logError);
        } elseif (isset($this->data[$firstKey]) && $this->_isThisClass($this->data[$firstKey])) {
            return $this->data[$firstKey]->get($path, $default, $logError);
        }
        return $default;
    }

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    protected function _makeSubData(mixed $key, mixed $value): \fan\core\base\data
    {
        $class = get_class($this);
        return new $class($value, $key, $this);
    }

    protected function _isThisClass(mixed $object): bool
    {
        return is_object($object) && get_class_alt($object) === get_class($this);
    }

    protected function _checkSetterClass(array $link, string $class): bool
    {
        return (string)$link['class'] === $class || !empty($link['object']) && $link['object'] instanceof $class;
    }

    protected function _getSubData(): array
    {
        $ret = [];
        $class = get_class($this);
        foreach ($this->data as $v) {
            if ($v instanceof $class) {
                $ret[] = $v;
            }
        }
        return $ret;
    }

    protected function _logError(int|string $errKey, array $replacement = []): static
    {
        $errMsg = $this->errMsg[$errKey];
        if (!isset($replacement['class'])) {
            $replacement['class'] = get_class($this);
        }
        foreach ($replacement as $k => $v) {
            $errMsg = str_replace('{' . strtoupper($k) . '}', $v, $errMsg);
        }
        $this->containerService('error')->logErrorMessage($errMsg, 'Data error', '', true);
        return $this;
    }

    // ======== The magic methods ======== \\

    /**
     * Handles dynamic property writes for this current component.
     *
     * @param mixed $value Value that should be applied or transformed.
     */
    public function __set(string $key, mixed $value): void
    {
        $this->set($key, $value);
    }

    /**
     * Handles dynamic property reads for this current component.
     */
    public function __get(string $key): mixed
    {
        return $this->get($key);
    }

    /**
     * Checks whether a dynamic property is available.
     */
    public function __isset(string $key): bool
    {
        return isset($this->data[$key]);
    }

    /**
     * Handles dynamic property removal for this current component.
     */
    public function __unset(string $key): void
    {
        if ($this->_checkSetter()) {
            unset($this->data[$key]);
        } else {
            $this->_logError(12, ['key' => $key]);
        }
    }

    /**
     * Implements PHP magic behavior for this current component.
     */
    public function __toString(): string
    {
        $ret = '';
        foreach ($this->data as $k => $v) {
            if (!empty($ret)) {
                $ret .= "\n";
            }
            $ret .= $k . ' => ';

            if (is_null($v)) {
                $ret .= '(NULL)';
            } elseif (is_bool($v)) {
                $ret .= '(boolean) ' . ($v ? 'TRUE' : 'FALSE');
            } elseif (is_scalar($v)) {
                $ret .= '(' . gettype($v) . ') ' . $v;
            } elseif (is_object($v)) {
                $ret .= '(Intanse Of ' . get_class($v) . ")\n";
                if (method_exists($v, '__toString')) {
                    $ret .= $v->__toString();
                } else {
                    // ToDo: Show Another objects
                }
            } elseif (is_array($v)) {
                $ret .= '(array) ';
                // ToDo: Show Array
            }
        }
        return $ret;
    }

    // ======== Required Interface methods ======== \\
    public function offsetExists(mixed $key): bool
    {
        return isset($this->data[$key]);
    }

    public function offsetGet(mixed $key): mixed
    {
        return $this->get($key);
    }

    public function offsetSet(mixed $key, mixed $value): void
    {
        $this->set($key, $value);
    }

    public function offsetUnset(mixed $key): void
    {
        unset($this->data[$key]);
    }

    public function current(): mixed
    {
        return current($this->data);
    }

    public function key(): mixed
    {
        return key($this->data);
    }

    public function next(): void
    {
        next($this->data);
    }

    public function rewind(): void
    {
        reset($this->data);
    }

    public function valid(): bool
    {
        return key($this->data) !== null;
    }

    /**
     * @return int Returns the numeric result produced by the operation.
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * Exports object state for PHP serialization.
     *
     * @return array Returns the structured data produced by the operation.
     */
    public function __serialize(): array
    {
        return [
            'multiLevel'  => $this->multiLevel,
            'fullRewrite' => $this->fullRewrite,
            'errMsg'      => $this->errMsg,
            'data'        => $this->data,
        ];
    }

    public function serialize(): string
    {
        return \fan\core\adapter\safe_serializer::encodePhpSnapshot($this->__serialize());
    }

    /**
     * Restores object state from PHP serialization data.
     */
    public function __unserialize(array $recover): void
    {
        $this->restoreSerializedData($recover);
    }

    public function unserialize(string $recover): void
    {
        $this->restoreSerializedData(
            \fan\core\adapter\safe_serializer::decodePhpSnapshot($recover, [])
        );
    }

    private function restoreSerializedData(array $recover): void
    {
        $this->multiLevel  = $recover['multiLevel'];
        $this->fullRewrite = $recover['fullRewrite'];
        $this->errMsg      = $recover['errMsg'];

        $this->data = $recover['data'];
        if ($this->multiLevel) {
            foreach ($this->data as $k => $v) {
                if (is_object($v) && $v instanceof \fan\core\base\data) {
                    $v->key      = $k;
                    $v->superior = $this;
                }
            }
        }
        // Attention: restore Setter in the children class by method _setSetter
    }

}
