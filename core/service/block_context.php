<?php

declare(strict_types=1);

namespace fan\core\service;

class block_context
{
    private \Closure $tabFactory;
    private \Closure $bootstrapRuntimeFactory;
    private \Closure $projectTabClassExists;

    public function __construct(
        callable $tabFactory,
        callable $bootstrapRuntimeFactory,
        private object $reflectionClassFactory,
        ?callable $projectTabClassExists = null
    )
    {
        $this->tabFactory = \Closure::fromCallable($tabFactory);
        $this->bootstrapRuntimeFactory = \Closure::fromCallable($bootstrapRuntimeFactory);
        $this->projectTabClassExists = \Closure::fromCallable(
            $projectTabClassExists
                ?? static fn(string $className): bool => class_exists($className, false)
        );
    }

    public function getCurrentBlockInfo(): array
    {
        if (!$this->projectTabClassExists('\fan\project\service\tab')) {
            return [null, null];
        }
        $tab   = $this->tab();
        $block = $tab->getCurrentBlock();
        if ($block) {
            $loader     = $this->bootstrapRuntime()->getLoader();
            $reflection = $this->reflectionClass($block);
            $path       = $reflection->getFileName();
            $realPath   = $loader->getRealPath($path);
            if ($realPath) {
                $path = str_replace($loader->project, '{PROJECT}', $realPath);
            }
        } else {
            $path = null;
        }

        return [$tab->getTabStage(), $path];
    }

    private function tab(): object
    {
        $tab = ($this->tabFactory)();
        if (!is_object($tab)) {
            throw new \UnexpectedValueException('Tab factory must return an object.');
        }

        return $tab;
    }

    private function bootstrapRuntime(): object
    {
        $runtime = ($this->bootstrapRuntimeFactory)();
        if (!is_object($runtime)) {
            throw new \UnexpectedValueException('Bootstrap runtime factory must return an object.');
        }

        return $runtime;
    }

    private function reflectionClass(object|string $object): \ReflectionClass
    {
        if (!method_exists($this->reflectionClassFactory, 'create')) {
            throw new \RuntimeException('Reflection class factory must expose create().');
        }

        $reflection = $this->reflectionClassFactory->create($object);
        if (!$reflection instanceof \ReflectionClass) {
            throw new \UnexpectedValueException('Reflection class factory must return a ReflectionClass.');
        }

        return $reflection;
    }

    private function projectTabClassExists(string $className): bool
    {
        return ($this->projectTabClassExists)($className);
    }
}
