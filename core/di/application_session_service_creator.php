<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_session_service_creator
{
    private \Closure $fatalExceptionFactory;

    public function __construct(?callable $fatalExceptionFactory = null)
    {
        $this->fatalExceptionFactory = \Closure::fromCallable(
            $fatalExceptionFactory
                ?? static function (mixed ...$arguments): \Throwable {
                    throw new \RuntimeException('Fatal exception factory is not configured for session service creator.');
                }
        );
    }

    public function createSessionService(
        container_interface $container,
        object $sessionState,
        callable $sessionServiceFactory,
        callable $sessionEngineFactory,
        mixed $nameSpace = null,
        mixed $group = 'custom'
    ): mixed {
        if ($group === null) {
            throw $this->createFatalException($container, 'Unset group name for \fan\core\service\session.');
        }
        if ($nameSpace === null) {
            $config = $container->get(service_id::CONFIG)->get('session');
            $group = 'app';
            $nameSpace = $container->get(service_id::APPLICATION)->getAppName();
            $replacementName = $config->get(['REPLACE_APP', $nameSpace]);
            if ($replacementName) {
                $nameSpace = (string)$replacementName;
            }
        }

        $nameSpace = (string)$nameSpace;
        $group = (string)$group;
        $instance = $sessionState->getInstance($group, $nameSpace);
        if ($instance === null) {
            $className = self::getProjectServiceClassName('session');
            if (!class_exists($className)) {
                throw new \InvalidArgumentException('Service "session" does not expose a project class.');
            }

            $instance = $sessionServiceFactory(
                $className,
                $nameSpace,
                $group,
                $container->get(service_id::CONFIG)->get('database'),
                $container->get(service_id::REQUEST_INPUT),
                static fn(): mixed => $container->get(service_id::ERROR),
                $container->get(service_id::REQUEST),
                null,
                static fn(string $namespace, string $group): mixed => $container->get(service_id::SESSION, $namespace, $group),
                static fn(string $date): mixed => $container->get(service_id::DATE, $date),
                static fn(mixed $path, mixed $domain): mixed => $container->get(service_id::COOKIE, $path, $domain),
                $container->get(service_id::PEAR_HTTP_SESSION_LOADER),
                $sessionEngineFactory,
                $sessionState,
                $container->get(service_id::BOOTSTRAP_RUNTIME),
                $container->get(service_id::CONFIG),
                static fn(string $type): mixed => $container->get(service_id::CACHE, $type),
                $container->get(service_id::PHP_RUNTIME_SETTINGS),
                $container->get(service_id::NATIVE_SESSION),
                $container->get(service_id::ARRAY_VALUE_READER)
            );
        }

        return $instance;
    }

    private function createFatalException(container_interface $container, string $message): \Throwable
    {
        $exception = ($this->fatalExceptionFactory)(
            $message,
            requestInput: $container->get(service_id::REQUEST_INPUT),
            exceptionHeaderWriter: $container->get(service_id::HEADER_WRITER)
        );
        if (!$exception instanceof \Throwable) {
            throw new \UnexpectedValueException('Fatal exception factory must return a throwable object.');
        }

        return $exception;
    }

    private static function getProjectServiceClassName(string $serviceName): string
    {
        return '\fan\project\service\\' . trim($serviceName, " \t\n\r\0\x0B\\");
    }
}
