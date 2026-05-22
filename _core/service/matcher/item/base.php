<?php
declare(strict_types=1);

namespace fan\core\service\matcher\item;
/**
 * Description of item
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
abstract class base implements \ArrayAccess, \Iterator
{
    /**
     * Current Index of Iterator
     * @var integer
     */
    protected int $index = 0;

    /**
     * Allowed property
     * @var array
     */
    protected array $data = [
    ];
    /**
     * Variable property
     * @var array
     */
    protected array $variable = [
    ];

    /**
     * Facade of service
     * @var \fan\core\service\matcher\item
     */
    protected ?object $item = null;

    /**
     * Facade of service
     * @var fan\core\base\service
     */
    protected ?object $facade = null;

    public function __construct(\fan\core\service\matcher\item $item)
    {
        $this->item = $item;
    }

    // ======== Static methods ======== \\
    // ======== The magic methods ======== \\

    /**
     * Handles dynamic property writes for this current component.
     *
     * @param mixed $value Value that should be applied or transformed.
     */
    public function __set(string $key, mixed $value): void
    {
        $this->set((string)$key, $value);
    }

    /**
     * Handles dynamic property reads for this current component.
     */
    public function __get(string $key): mixed
    {
        return $this->get((string)$key);
    }

    // ======== Required Interface methods ======== \\

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
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
        $this->_checkKey($key);
        return !empty($this->data[$key]);
    }

    public function offsetUnset(mixed $key): void
    {
        $key = (string)$key;
        $this->_checkKey($key);
        $this->_makeException('Isn\'t allowed unset subproperty of item of matcher.');
    }

    public function rewind(): void
    {
        $this->index = 0;
    }

    public function current(): mixed
    {
        return $this->valid() ? $this->data[$this->_getCurrentKey()] : null;
    }

    public function key(): int
    {
        return $this->index;
    }

    public function next(): void
    {
        ++$this->index;
    }

    public function valid(): bool
    {
        return !is_null($this->_getCurrentKey());
    }
    // ======== Main Interface methods ======== \\

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    public function set(string $key, mixed $value): static
    {
        $method = $this->_checkKey($key, 'set');
        if (!is_null($this->data[$key]) && !in_array($key, $this->variable)) {
            throw new \LogicException('Error. Try to change existing property.');
        } elseif (method_exists($this, $method)) {
            $this->$method($key, $value);
        } else {
            $this->data[$key] = $value;
        }
        return $this;
    }

    public function get(string $key): mixed
    {
        $method = $this->_checkKey($key, 'get');
        return method_exists($this, $method) ? $this->$method() : $this->data[$key];
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function setFacade(\fan\core\service\matcher $facade): static
    {
        $this->facade = $facade;
        return $this;
    }

    // ======== Private/Protected methods ======== \\

    protected function _checkKey(string $key, string $method = ''): string
    {
        if (!array_key_exists($key, $this->data)) {
            $this->_makeException('Invalid key "' . $key . '" while accessing the item property of matcher.');
        }
        if (!empty($method)) {
            foreach (explode('_', $key) as $v) {
                $method .= ucfirst($v);
            }
        }
        return $method;
    }

    protected function _getCurrentKey(): ?string
    {
        $keys = array_keys($this->data);
        return isset($keys[$this->index]) ? $keys[$this->index] : null;
    }

    /**
     * @throws \fan\project\exception\service\fatal
     * @throws \fan\project\exception\fatal
     */
    protected function _makeException(string $errMsg): never
    {
        if ($this->facade) {
            throw new \fan\project\exception\service\fatal($this->facade, $errMsg);
        }
        throw new \fan\project\exception\fatal($errMsg);
    }

    /**
     * @param mixed $default Fallback value returned when no explicit value is available.
     */
    public function _getConfig(mixed $key, mixed $default = null): mixed
    {
        return $this->facade->getConfig()->get($key, $default);
    }

}
