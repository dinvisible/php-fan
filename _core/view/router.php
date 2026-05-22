<?php
declare(strict_types=1);

namespace fan\core\view;
/**
 * View element of Block
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
abstract class router implements \ArrayAccess, \Countable
{
    /**
     * Parent block
     * @var \fan\core\block\base
     */
    protected ?object $block = null;
    /**
     * Array of Keepers
     * @var array
     */
    protected array $keepers = [];
    /**
     * Default Route Keeper
     * @var string
     */
    protected ?string $defaultKey = null;

    public function __construct(\fan\core\block\base $block)
    {
        if (empty($this->keepers) || !is_array($this->keepers)) {
            throw new \fan\project\exception\block\fatal($this, 'Keepers list doesn\'t set at the class "' . get_class($this) . '"');
        }
        if (empty($this->defaultKey)) {
            reset($this->keepers);
            $this->defaultKey = key($this->keepers);
        } elseif (!array_key_exists($this->defaultKey, $this->keepers)) {
            throw new \fan\project\exception\block\fatal($this, 'Incorrect default Keepers key "' . $this->defaultKey . '" at the class "' . get_class($this) . '"');
        }
        $this->block = $block;
    }
    // ======== Static methods ======== \\
    // ======== The magic methods ======== \\

    public function __set(string $key, mixed $value): void
    {
        $this->set((string)$key, $value);
    }

    public function __get(string $key): mixed
    {
        return $this->get((string)$key);
    }

    public function __isset(string $key): bool
    {
        $key = (string)$key;
        return !empty($this->keepers[$key]);
    }

    // ======== Required Interface methods ======== \\

    public function offsetSet(mixed $key, mixed $value): void
    {
        $this->set((string)$key, $value);
    }

    public function offsetGet(mixed $key): mixed
    {
        return $this->get((string)$key);
    }

    public function offsetExists(mixed $key): bool
    {
        $key = (string)$key;
        return array_key_exists($key, $this->keepers);
    }

    public function offsetUnset(mixed $key): void
    {
    } // offsetUnset
    // ======== Main Interface methods ======== \\
    public function set(mixed $key, mixed $value): static
    {
        $key = (string)$key;
        if ($this->_checkSetter()) {
            if (array_key_exists($key, $this->keepers)) {
                $this->_getKeeper($key)->set(null, $value);
            } else {
                $method = 'set' . ucfirst(strtolower($this->defaultKey));
                if (method_exists($this, $method)) {
                    $this->$method($value);
                } else {
                    $keeper = $this->_getKeeper((string)$this->defaultKey);
                    $keeper->set($key, $value);
                }
            }
        }
        return $this;
    }

    public function get(string $key, mixed $default = null, bool $logError = true): mixed
    {
        $method = 'get' . ucfirst(strtolower($key));
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        if (array_key_exists($key, $this->keepers)) {
            return $this->_getKeeper($key);
        }
        $keeper = $this->_getKeeper((string)$this->defaultKey);
        return $keeper->get($key, $default, $logError);
    }

    public function setSeveral(array $values): static
    {
        foreach ($values as $k => $v) {
            $this->set($k, $v);
        }
        return $this;
    }

    public function getBlock(): \fan\core\block\base
    {
        return $this->block;
    }

    public function toArray(): array
    {
        return $this->getAll();
    }

    public function getAll(): array
    {
        if (count($this->keepers) === 1) {
            return $this->_getKeeper((string)$this->defaultKey)->toArray();
        }
        $result = [];
        foreach ($this->keepers as $k => $v) {
            $result[$k] = adduceToArray($v);
        }
        return $result;
    }

    public function count(): int
    {
        return count($this->keepers);
    }

    // ======== Private/Protected methods ======== \\
    /**
     * @throws \fan\core\exception\block\fatal
     */
    public function _getKeeper(string $key): \fan\core\view\keeper
    {
        if (!array_key_exists($key, $this->keepers)) {
            throw new \fan\project\exception\block\fatal($this, 'Incorrect name of Keeper "' . $key . '"');
        }
        if (empty($this->keepers[$key])) {
            $method = '_get' . ucfirst($key) . 'Keeper';
            $this->keepers[$key] = method_exists($this, $method) ? $this->$method() : new \fan\project\view\keeper($this);
        }
        return $this->keepers[$key];
    }

    protected function _checkSetter(): bool
    {
        $trace = debug_backtrace();
        foreach ($trace as $v) {
            if (!isset($v['object'])) {
                return false;
            }
            if ($v['object'] !== $this) {
                return $v['object'] === $this->block;
            }
        }
        return false;
    }
}
