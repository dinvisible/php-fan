<?php

declare(strict_types=1);

namespace fan\core\di;

final class configured_class_instantiator
{
    private ?object $reflectionClassFactory = null;

    public function __construct(?object $reflectionClassFactory = null)
    {
        $this->reflectionClassFactory = $reflectionClassFactory;
    }

    public function __invoke(string $className, array $arguments): object
    {
        return $this->reflectionClass($className)->newInstanceArgs($arguments);
    }

    private function reflectionClass(object|string $className): \ReflectionClass
    {
        if ($this->reflectionClassFactory === null) {
            throw new \RuntimeException('Reflection class factory is not configured for configured class instantiator.');
        }
        if (!method_exists($this->reflectionClassFactory, 'create')) {
            throw new \BadMethodCallException('Reflection class factory must expose create().');
        }

        $reflection = $this->reflectionClassFactory->create($className);
        if (!$reflection instanceof \ReflectionClass) {
            throw new \UnexpectedValueException('Reflection class factory must return a ReflectionClass.');
        }

        return $reflection;
    }
}
