<?php

declare(strict_types=1);

namespace fan\core\di;

final class legacy_service_factory implements service_factory_interface
{
    public function has(string $serviceName): bool
    {
        $className = $this->getServiceClassName($serviceName);

        return class_exists($className) && method_exists($className, 'instance');
    }

    public function create(string $serviceName, array $arguments = []): mixed
    {
        $className = $this->getServiceClassName($serviceName);
        if (!class_exists($className) || !method_exists($className, 'instance')) {
            throw new \InvalidArgumentException('Service "' . $serviceName . '" does not expose an instance factory.');
        }

        return $arguments === [] ? $className::instance() : call_user_func_array([$className, 'instance'], $arguments);
    }

    private function getServiceClassName(string $serviceName): string
    {
        return '\fan\project\service\\' . trim($serviceName, " \t\n\r\0\x0B\\");
    }
}
