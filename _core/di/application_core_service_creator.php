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
            $container->get('request_input'),
            $container->get('bootstrap_runtime'),
            static fn(bool $useBase64 = false): mixed => $container->get('json', $useBase64),
            static fn(): mixed => $container->get('cookie'),
            static fn(): mixed => $container->get('matcher'),
            $container->get('bootstrap_runtime'),
            $container->get('config'),
            static fn(string $type): mixed => $container->get('cache', $type),
            $container->get('array_adducer'),
            $container->get('recursive_merger'),
            $container->get('array_value_reader'),
            $container->get('class_name_resolver')
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
            static fn(bool $checkLogout): mixed => $container->get($checkLogout ? 'current_user_checked' : 'current_user'),
            static fn(string $namespace, string $group): mixed => $container->get('session', $namespace, $group),
            $container->get('error'),
            static fn(): mixed => $container->get('current_user_space'),
            static fn(string $date, mixed $format = null): mixed => $container->get('date', $date, $format),
            $container->get('bootstrap_runtime'),
            $container->get('config'),
            static fn(string $type): mixed => $container->get('cache', $type)
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
            $container->get('bootstrap_runtime'),
            $container->get('config'),
            static fn(string $type): mixed => $container->get('cache', $type),
            $container->get('reflection_class_factory')
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
            $container->get('bootstrap_runtime'),
            $container->get('bootstrap_runtime'),
            $container->get('config'),
            static fn(string $type): mixed => $container->get('cache', $type),
            $container->get('array_adducer')
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
            $container->get('tab'),
            $container->get('request_input'),
            $container->get('bootstrap_runtime'),
            $container->get('config'),
            static fn(string $type): mixed => $container->get('cache', $type),
            $container->get('meta_file_storage'),
            $container->get('array_adducer'),
            $container->get('reflection_class_factory')
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
            $container->get('request_input'),
            $container->get('header_writer'),
            $container->get('bootstrap_runtime'),
            $container->get('config'),
            static fn(string $type): mixed => $container->get('cache', $type),
            $container->get('recursive_merger')
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
            $container->get('request_input'),
            $container->get('bootstrap_runtime'),
            null,
            null,
            $container->get('bootstrap_runtime'),
            $container->get('config'),
            static fn(string $type): mixed => $container->get('cache', $type),
            $container->get('php_array_file_loader'),
            $container->get('error_log_writer'),
            $container->get('error_file_storage')
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
            $container->get('request_input'),
            $container->get('bootstrap_runtime'),
            $container->get('locale'),
            $container->get('application'),
            $container->get('matcher_route_file_storage'),
            $matcherItemFactory,
            $matcherItemComponentFactory,
            $container->get('bootstrap_runtime'),
            $container->get('config'),
            static fn(string $type): mixed => $container->get('cache', $type)
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
            $container->get('bootstrap_runtime'),
            static fn(?string $date = null, mixed $format = null): mixed => $container->get('date', $date, $format),
            static fn(): mixed => $container->get('entity'),
            static fn(): mixed => $container->get('error'),
            null,
            null,
            $timerProgramFactory,
            $container->get('bootstrap_runtime'),
            $container->get('config'),
            static fn(string $type): mixed => $container->get('cache', $type)
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
            static fn(): mixed => $container->get('entity'),
            static fn(): mixed => $container->get('tab'),
            static fn(string $namespace, string $group): mixed => $container->get('session', $namespace, $group),
            static fn(): mixed => $container->get('request'),
            static fn(mixed $path = null, mixed $domain = null): mixed => $container->get('cookie', $path, $domain),
            static fn(): mixed => $container->get('matcher'),
            $container->get('bootstrap_runtime'),
            $container->get('config'),
            static fn(string $type): mixed => $container->get('cache', $type),
            $container->get('array_adducer'),
            $container->get('class_name_resolver')
        );
    }

    private static function getProjectServiceClassName(string $serviceName): string
    {
        return '\fan\project\service\\' . trim($serviceName, " \t\n\r\0\x0B\\");
    }
}
