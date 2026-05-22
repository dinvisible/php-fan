<?php

declare(strict_types=1);

namespace fan\core\di;

trait container_aware_trait
{
    private ?container_interface $serviceContainer = null;

    public function setServiceContainer(?container_interface $container): static
    {
        $this->serviceContainer = $container;

        return $this;
    }

    protected function getServiceContainer(): ?container_interface
    {
        if ($this->serviceContainer !== null) {
            return $this->serviceContainer;
        }

        return self::defaultServiceContainer();
    }

    protected function containerService(string $serviceName, mixed ...$arguments): mixed
    {
        return self::resolveContainerService($this->getServiceContainer(), $serviceName, $arguments);
    }

    protected static function staticContainerService(string $serviceName, mixed ...$arguments): mixed
    {
        return self::resolveContainerService(self::defaultServiceContainer(), $serviceName, $arguments);
    }

    private static function defaultServiceContainer(): ?container_interface
    {
        if (function_exists('\service_container')) {
            return \service_container();
        }

        return null;
    }

    private static function resolveContainerService(?container_interface $container, string $serviceName, array $arguments): mixed
    {
        if ($container !== null && $container->has($serviceName)) {
            return $container->get($serviceName, ...$arguments);
        }

        if (function_exists('\service_factory')) {
            $factory = \service_factory();
            if ($factory->has($serviceName)) {
                return $factory->create($serviceName, $arguments);
            }
        }

        if (function_exists('\service')) {
            $service = \service($serviceName, $arguments);
            if ($service !== null) {
                return $service;
            }
        }

        $projectServiceClass = '\fan\project\service\\' . $serviceName;
        if (class_exists($projectServiceClass, false) && method_exists($projectServiceClass, 'instance')) {
            return $projectServiceClass::instance(...$arguments);
        }

        return null;
    }
}
