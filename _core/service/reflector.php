<?php
declare(strict_types=1);

namespace fan\core\service;
/**
 * Description of reflector
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
class reflector extends \fan\core\base\service\single
{
    private array $reflection = [];
    private array $parentChain = [];

    public function setReflection(mixed &$className): static
    {
        if (is_object($className)) {
            $className = get_class($className);
        }
        $className = (string)$className;
        if (isset($this->reflection[$className])) {
            return $this;
        }

        $parentChain = [];
        $reflection  = new \ReflectionClass($className);
        while (!empty($reflection)) {
            $tmpName = $reflection->getName();
            if (isset($this->reflection[$tmpName])) {
                $parentChain = array_merge($parentChain, $this->parentChain[$tmpName]);
                break;
            }

            $parentChain[$tmpName]      = $reflection;
            $this->reflection[$tmpName] = $reflection;

            $reflection = $reflection->getParentClass();
        }

        foreach (array_keys($parentChain) as $k) {
            if (isset($this->parentChain[$k])) {
                break;
            }
            $this->parentChain[$k] = $parentChain;
            array_shift($parentChain);
        }
        return $this;
    }

    public function getReflection(object|string $className): \ReflectionClass
    {
        $this->setReflection($className);
        return $this->reflection[$className];
    }

    public function getParentChain(object|string $className): array
    {
        $this->setReflection($className);
        return $this->parentChain[$className];
    }

    public function getParentPaths(object|string $className): array
    {
        $paths = [];
        $chain = $this->getParentChain($className);
        foreach ($chain as $k => $v) {
            $paths[$k] = $v->getFileName();
        }
        return $paths;
    }
}
