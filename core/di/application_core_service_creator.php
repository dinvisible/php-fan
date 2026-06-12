<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_core_service_creator
{
    public function createRequestService(container_interface $container, callable $requestServiceFactory): mixed
    {
        $className = self::getProjectServiceClassName('request');
        if (!class_exists($className)) {
            throw new \InvalidArgumentException('Service "request" does not expose a project class.');
        }

        return $requestServiceFactory(
            $className,
            $container->get(service_id::REQUEST_INPUT),
            $container->get(service_id::BOOTSTRAP_RUNTIME),
            static fn(bool $useBase64 = false): mixed => $container->get(service_id::JSON, $useBase64),
            static fn(): mixed => $container->get(service_id::COOKIE),
            static fn(): mixed => $container->get(service_id::MATCHER),
            $container->get(service_id::BOOTSTRAP_RUNTIME),
            $container->get(service_id::CONFIG),
            static fn(string $type): mixed => $container->get(service_id::CACHE, $type),
            $container->get(service_id::ARRAY_ADDUCER),
            $container->get(service_id::RECURSIVE_MERGER),
            $container->get(service_id::ARRAY_VALUE_READER),
            $container->get(service_id::CLASS_NAME_RESOLVER)
        );
    }

    public function createRoleService(container_interface $container, callable $roleServiceFactory): mixed
    {
        $className = self::getProjectServiceClassName('role');
        if (!class_exists($className)) {
            throw new \InvalidArgumentException('Service "role" does not expose a project class.');
        }

        return $roleServiceFactory(
            $className,
            static fn(bool $checkLogout): mixed => $container->get($checkLogout ? service_id::CURRENT_USER_CHECKED : service_id::CURRENT_USER),
            static fn(string $namespace, string $group): mixed => $container->get(service_id::SESSION, $namespace, $group),
            $container->get(service_id::ERROR),
            static fn(): mixed => $container->get(service_id::CURRENT_USER_SPACE),
            static fn(string $date, mixed $format = null): mixed => $container->get(service_id::DATE, $date, $format),
            $container->get(service_id::BOOTSTRAP_RUNTIME),
            $container->get(service_id::CONFIG),
            static fn(string $type): mixed => $container->get(service_id::CACHE, $type)
        );
    }

    public function createReflectorService(container_interface $container, callable $reflectorServiceFactory): mixed
    {
        $className = self::getProjectServiceClassName('reflector');
        if (!class_exists($className)) {
            throw new \InvalidArgumentException('Service "reflector" does not expose a project class.');
        }

        return $reflectorServiceFactory(
            $className,
            $container->get(service_id::BOOTSTRAP_RUNTIME),
            $container->get(service_id::CONFIG),
            static fn(string $type): mixed => $container->get(service_id::CACHE, $type),
            $container->get(service_id::REFLECTION_CLASS_FACTORY)
        );
    }

    public function createApplicationService(container_interface $container, callable $applicationServiceFactory): mixed
    {
        $className = self::getProjectServiceClassName('application');
        if (!class_exists($className)) {
            throw new \InvalidArgumentException('Service "application" does not expose a project class.');
        }

        return $applicationServiceFactory(
            $className,
            true,
            $container->get(service_id::BOOTSTRAP_RUNTIME),
            $container->get(service_id::BOOTSTRAP_RUNTIME),
            $container->get(service_id::CONFIG),
            static fn(string $type): mixed => $container->get(service_id::CACHE, $type),
            $container->get(service_id::ARRAY_ADDUCER)
        );
    }

    public function createDebugService(container_interface $container, callable $debugServiceFactory): mixed
    {
        $className = self::getProjectServiceClassName('debug');
        if (!class_exists($className)) {
            throw new \InvalidArgumentException('Service "debug" does not expose a project class.');
        }

        return $debugServiceFactory(
            $className,
            true,
            $container->get(service_id::TAB),
            $container->get(service_id::REQUEST_INPUT),
            $container->get(service_id::BOOTSTRAP_RUNTIME),
            $container->get(service_id::CONFIG),
            static fn(string $type): mixed => $container->get(service_id::CACHE, $type),
            $container->get(service_id::META_FILE_STORAGE),
            $container->get(service_id::ARRAY_ADDUCER),
            $container->get(service_id::REFLECTION_CLASS_FACTORY)
        );
    }

    public function createHeaderService(container_interface $container, callable $headerServiceFactory): mixed
    {
        $className = self::getProjectServiceClassName('header');
        if (!class_exists($className)) {
            throw new \InvalidArgumentException('Service "header" does not expose a project class.');
        }

        return $headerServiceFactory(
            $className,
            true,
            $container->get(service_id::REQUEST_INPUT),
            $container->get(service_id::HEADER_WRITER),
            $container->get(service_id::BOOTSTRAP_RUNTIME),
            $container->get(service_id::CONFIG),
            static fn(string $type): mixed => $container->get(service_id::CACHE, $type),
            $container->get(service_id::RECURSIVE_MERGER)
        );
    }

    public function createErrorService(container_interface $container, callable $errorServiceFactory): mixed
    {
        $className = self::getProjectServiceClassName('error');
        if (!class_exists($className)) {
            throw new \InvalidArgumentException('Service "error" does not expose a project class.');
        }

        return $errorServiceFactory(
            $className,
            true,
            $container->get(service_id::REQUEST_INPUT),
            $container->get(service_id::BOOTSTRAP_RUNTIME),
            null,
            null,
            $container->get(service_id::BOOTSTRAP_RUNTIME),
            $container->get(service_id::CONFIG),
            static fn(string $type): mixed => $container->get(service_id::CACHE, $type),
            $container->get(service_id::PHP_ARRAY_FILE_LOADER),
            $container->get(service_id::ERROR_LOG_WRITER),
            $container->get(service_id::ERROR_FILE_STORAGE)
        );
    }

    public function createMatcherService(
        container_interface $container,
        callable $matcherItemFactory,
        callable $matcherItemComponentFactory,
        callable $matcherServiceFactory
    ): mixed
    {
        $className = self::getProjectServiceClassName('matcher');
        if (!class_exists($className)) {
            throw new \InvalidArgumentException('Service "matcher" does not expose a project class.');
        }

        return $matcherServiceFactory(
            $className,
            true,
            $container->get(service_id::REQUEST_INPUT),
            $container->get(service_id::BOOTSTRAP_RUNTIME),
            $container->get(service_id::LOCALE),
            $container->get(service_id::APPLICATION),
            $container->get(service_id::MATCHER_ROUTE_FILE_STORAGE),
            $matcherItemFactory,
            $matcherItemComponentFactory,
            $container->get(service_id::BOOTSTRAP_RUNTIME),
            $container->get(service_id::CONFIG),
            static fn(string $type): mixed => $container->get(service_id::CACHE, $type)
        );
    }

    public function createTimerService(
        container_interface $container,
        callable $timerProgramFactory,
        callable $timerServiceFactory
    ): mixed
    {
        $className = self::getProjectServiceClassName('timer');
        if (!class_exists($className)) {
            throw new \InvalidArgumentException('Service "timer" does not expose a project class.');
        }

        return $timerServiceFactory(
            $className,
            $container->get(service_id::BOOTSTRAP_RUNTIME),
            static fn(?string $date = null, mixed $format = null): mixed => $container->get(service_id::DATE, $date, $format),
            static fn(): mixed => $container->get(service_id::ENTITY),
            static fn(): mixed => $container->get(service_id::ERROR),
            null,
            null,
            $timerProgramFactory,
            $container->get(service_id::BOOTSTRAP_RUNTIME),
            $container->get(service_id::CONFIG),
            static fn(string $type): mixed => $container->get(service_id::CACHE, $type)
        );
    }

    public function createLocaleService(container_interface $container, callable $localeServiceFactory): mixed
    {
        $className = self::getProjectServiceClassName('locale');
        if (!class_exists($className)) {
            throw new \InvalidArgumentException('Service "locale" does not expose a project class.');
        }

        return $localeServiceFactory(
            $className,
            true,
            static fn(): mixed => $container->get(service_id::ENTITY),
            static fn(): mixed => $container->get(service_id::TAB),
            static fn(string $namespace, string $group): mixed => $container->get(service_id::SESSION, $namespace, $group),
            static fn(): mixed => $container->get(service_id::REQUEST),
            static fn(mixed $path = null, mixed $domain = null): mixed => $container->get(service_id::COOKIE, $path, $domain),
            static fn(): mixed => $container->get(service_id::MATCHER),
            $container->get(service_id::BOOTSTRAP_RUNTIME),
            $container->get(service_id::CONFIG),
            static fn(string $type): mixed => $container->get(service_id::CACHE, $type),
            $container->get(service_id::ARRAY_ADDUCER),
            $container->get(service_id::CLASS_NAME_RESOLVER)
        );
    }

    private static function getProjectServiceClassName(string $serviceName): string
    {
        return '\fan\project\service\\' . trim($serviceName, " \t\n\r\0\x0B\\");
    }
}
