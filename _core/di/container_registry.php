<?php

declare(strict_types=1);

namespace fan\core\di;

/**
 * Stores the application-wide service container.
 *
 * This registry is the bridge between legacy static service access and the new
 * container-based factory layer.
 */
final class container_registry
{
    private static ?container_interface $container = null;

    private static ?service_factory_interface $serviceFactory = null;

    public static function get(): container_interface
    {
        if (self::$container === null) {
            self::$container = self::createDefaultContainer();
        }

        return self::$container;
    }

    public static function set(container_interface $container): void
    {
        self::$container = $container;
    }

    public static function getFactory(): service_factory_interface
    {
        if (self::$serviceFactory === null) {
            self::$serviceFactory = new legacy_service_factory();
        }

        return self::$serviceFactory;
    }

    public static function setFactory(service_factory_interface $serviceFactory): void
    {
        self::$serviceFactory = $serviceFactory;
        self::$container = null;
    }

    public static function reset(): void
    {
        self::$container = null;
        self::$serviceFactory = null;
    }

    private static function createDefaultContainer(): container
    {
        $container = new container();
        $serviceFactory = self::getFactory();

        $container
            ->factory('config', static fn(container_interface $container, mixed ...$arguments): mixed => $serviceFactory->create('config', $arguments))
            ->factory('database', static fn(container_interface $container, mixed ...$arguments): mixed => $serviceFactory->create('database', $arguments), false)
            ->factory('database_by_param', static fn(container_interface $container, mixed $param, mixed $extraKey = 0): mixed => \fan\project\service\database::instanceByParam($param, $extraKey), false)
            ->factory('request', static fn(container_interface $container): mixed => $serviceFactory->create('request'))
            ->factory('cache', static fn(container_interface $container, mixed ...$arguments): mixed => $serviceFactory->create('cache', $arguments), false)
            ->factory('session', static fn(container_interface $container, mixed ...$arguments): mixed => $serviceFactory->create('session', $arguments), false)
            ->factory('email', static fn(container_interface $container, mixed ...$arguments): mixed => $serviceFactory->create('email', $arguments), false)
            ->factory('role', static fn(container_interface $container): mixed => $serviceFactory->create('role'))
            ->factory('entity', static fn(container_interface $container, mixed ...$arguments): mixed => $serviceFactory->create('entity', $arguments), false)
            ->factory('tab', static fn(container_interface $container): mixed => $serviceFactory->create('tab'))
            ->factory('template', static fn(container_interface $container): mixed => $serviceFactory->create('template'))
            ->factory('json', static fn(container_interface $container, mixed ...$arguments): mixed => $serviceFactory->create('json', $arguments), false)
            ->factory('error', static fn(container_interface $container): mixed => $serviceFactory->create('error'))
            ->factory('header', static fn(container_interface $container): mixed => $serviceFactory->create('header'))
            ->factory('locale', static fn(container_interface $container): mixed => $serviceFactory->create('locale'))
            ->factory('application', static fn(container_interface $container): mixed => $serviceFactory->create('application'));

        return $container;
    }
}
