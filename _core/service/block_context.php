<?php

declare(strict_types=1);

namespace fan\core\service;

use fan\core\di\container_interface;

class block_context
{
    public function __construct(
        private container_interface $container,
        private object $reflectionClassFactory
    )
    {
    }

    public function getCurrentBlockInfo(): array
    {
        if (!class_exists('\fan\project\service\tab', false)) {
            return [null, null];
        }
        $tab   = $this->container->get('tab');
        $block = $tab->getCurrentBlock();
        if ($block) {
            $loader     = $this->container->get('bootstrap_runtime')->getLoader();
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
}
