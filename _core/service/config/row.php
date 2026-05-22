<?php

declare(strict_types=1);

namespace fan\core\service\config;
/**
 * Meta Data Row
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
class row extends \fan\core\base\data
{
    /**
     * Saved source data
     * @var array
     */
    protected array $srcData = [];

    /**
     * Facade of service
     * @var fan\core\base\service
     */
    protected ?object $facade = null;

    /**
     * Services - owners of config
     * @var array
     */
    protected array $owners = [];

    /**
     * Rooy Key of element
     * @var string
     */
    protected ?string $rootKey = null;

    public function __construct(mixed $data, int|string|null $key = null, ?\fan\core\base\data $superior = null)
    {
        parent::__construct($data, $key, $superior);

        $this->srcData = $this->data;
        //$this->errMsg[91] = 'Facade isn\'t set';
    }

    // ======== Main Interface methods ======== \\
    public function setFacade(\fan\core\base\service $facade): static
    {
        if (empty($this->facade)) {
            $this->facade = $facade;
            if (in_array($facade->getConfigType(), ['service', 'entity', 'cli', 'plain'])) {
                $this->_setSetter($facade);
            }
        }
        foreach ($this->_getSubData() as $v) {
            $v->setFacade($facade);
        }
        return $this;
    }

    public function getRootKey(): string
    {
        if (is_null($this->rootKey)) {
            if (empty($this->superior)) {
                $this->rootKey = '';
            } else {
                $rootKey = $this->superior->getRootKey();
                $this->rootKey = empty($rootKey) ? $this->key : $rootKey;
            }
        }
        return $this->rootKey;
    }

    public function setServiceOwner(\fan\core\base\service $service): static
    {
        $name = get_class_name($service);
        if ($name === $this->getRootKey()) {
            $this->_setSetter($service);
            $this->owners[] = $service;
            foreach ($this->_getSubData() as $v) {
                $v->setServiceOwner($service);
            }
        }
        return $this;
    }
    public function setPlainOwner(object $ctrl, string $name): static
    {
        if ($name === $this->getRootKey()) {
            $this->_setSetter($ctrl);
            $this->owners[] = $ctrl;
            foreach ($this->_getSubData() as $v) {
                $v->setPlainOwner($ctrl);
            }
        }
        return $this;
    }

    public function setEntityOwner(\fan\core\base\model\entity $entity, string $name): static
    {
        if ($name === $this->getRootKey()) {
            $this->_setSetter($entity);
            $this->owners[] = $entity;
            foreach ($this->_getSubData() as $v) {
                $v->setEntityOwner($entity);
            }
        }
        return $this;
    }
    public function getOwners(): array
    {
        return $this->owners;
    }

    public function getSources(): array
    {
        $clone = clone $this;
        $clone->reset();
        return $clone->toArray();
    }

    public function reset(mixed $key = null): static
    {
        if ($this->_checkSetter()) {
            if (is_null($key)) {
                $this->data = $this->srcData;
                foreach ($this->_getSubData() as $v) {
                    $v->reset(null);
                }
            }  elseif (!isset($this->srcData[$key])) {
                $this->data[$key] = null;
            }  elseif (is_scalar($this->srcData[$key])) {
                $this->data[$key] = $this->srcData[$key];
            } else {
                $this->set($key, $this->srcData[$key]);
            }
        }
        return $this;
    }

    public function mergeData(array|\fan\core\service\config\row $data, bool $priority = true): static
    {
        if (is_object($data) && $data instanceof \fan\core\service\config\row) {
            $data = $data->toArray();
        }
        if ($this->_checkSetter() && is_array($data)) {
            foreach ($data as $k => $v) {
                if ($priority || !isset($this->data[$k])) {
                    $this->set($k, $v);
                }
            }
        }
        return $this;
    }

    // ======== Private/Protected methods ======== \\
    protected function _makeSubData(mixed $key, mixed $value): \fan\core\service\config\row
    {
        $class = get_class($this);
        $subData = new $class($value, $key, $this);
        if (!empty($this->facade)) {
            $subData->setFacade($this->facade);
        }
        return $subData;
    }

    protected function _checkSetter(): bool
    {
        //$this->facade->
        return parent::_checkSetter();
    }
    // ======== The magic methods ======== \\
    /**
     * Implements PHP magic behavior for this current component.
     */
    public function __clone()
    {
        $this->reset(null);
    }

    /**
     * Handles dynamic property removal for this current component.
     */
    public function __unset(string $key): void
    {
        // Todo: Do this "throw" only if it is enabled in config
        throw new \fan\project\exception\service\fatal($this->facade, 'You can\'t unset data for key "' . $key . '".');
    }

    // ======== Required Interface methods ======== \\

    /**
     * Exports object state for PHP serialization.
     *
     * @return array Returns the structured data produced by the operation.
     */
    public function __serialize(): array
    {
        return [
            'parent'  => parent::__serialize(),
            'srcData' => $this->srcData,
            'rootKey' => $this->rootKey,
        ];
    }

    /**
     * Restores object state from PHP serialization data.
     */
    public function __unserialize(array $recover): void
    {
        parent::__unserialize($recover['parent']);

        $this->srcData = $recover['srcData'];
        $this->rootKey = $recover['rootKey'];
    }

    public function serialize(): string
    {
        return \fan\core\adapter\safe_serializer::encodePhpSnapshot($this->__serialize());
    }

    public function unserialize(string $recover): void
    {
        $recover = \fan\core\adapter\safe_serializer::decodePhpSnapshot($recover, []);

        parent::__unserialize($recover['parent']);

        $this->srcData = $recover['srcData'];
        $this->rootKey = $recover['rootKey'];
    }

}
