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
            $config = $container->get('config')->get('session');
            $group = 'app';
            $nameSpace = $container->get('application')->getAppName();
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
                $container->get('config')->get('database'),
                $container->get('request_input'),
                static fn(): mixed => $container->get('error'),
                $container->get('request'),
                null,
                static fn(string $namespace, string $group): mixed => $container->get('session', $namespace, $group),
                static fn(string $date): mixed => $container->get('date', $date),
                static fn(mixed $path, mixed $domain): mixed => $container->get('cookie', $path, $domain),
                $container->get('pear_http_session_loader'),
                $sessionEngineFactory,
                $sessionState,
                $container->get('bootstrap_runtime'),
                $container->get('config'),
                static fn(string $type): mixed => $container->get('cache', $type),
                $container->get('php_runtime_settings'),
                $container->get('native_session'),
                $container->get('array_value_reader')
            );
        }

        return $instance;
    }

    private function createFatalException(container_interface $container, string $message): \Throwable
    {
        $exception = ($this->fatalExceptionFactory)(
            $message,
            requestInput: $container->get('request_input'),
            exceptionHeaderWriter: $container->get('header_writer')
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
