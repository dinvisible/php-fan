<?php
declare(strict_types=1);

namespace fan\core\service;
use fan\core\base\service\single;

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
class reflector extends single
{
    private array $reflection = [];
    private array $parentChain = [];
    private object $reflectionClassFactory;

    public function __construct(
        ?object $serviceBootstrapRuntime = null,
        ?object $serviceConfigurator = null,
        ?callable $serviceCacheFactory = null,
        ?object $reflectionClassFactory = null
    ) {
        if ($reflectionClassFactory !== null) {
            $this->reflectionClassFactory = $reflectionClassFactory;
        }
        parent::__construct(true, $serviceBootstrapRuntime, $serviceConfigurator, $serviceCacheFactory);
    }

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
        $reflection  = $this->reflectionClass($className);
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

    private function reflectionClass(object|string $className): \ReflectionClass
    {
        if (!isset($this->reflectionClassFactory) || !method_exists($this->reflectionClassFactory, 'create')) {
            throw new \RuntimeException('Reflection class factory must expose create().');
        }

        $reflection = $this->reflectionClassFactory->create($className);
        if (!$reflection instanceof \ReflectionClass) {
            throw new \UnexpectedValueException('Reflection class factory must return a ReflectionClass.');
        }

        return $reflection;
    }
}
