<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_session_service_creator
{
    private \Closure $fatalExceptionFactory;
    private \Closure $projectServiceClassExists;

    public function __construct(?callable $fatalExceptionFactory = null, ?callable $projectServiceClassExists = null)
    {
        $this->fatalExceptionFactory = \Closure::fromCallable(
            $fatalExceptionFactory
                ?? static function (mixed ...$arguments): \Throwable {
                    throw new \RuntimeException('Fatal exception factory is not configured for session service creator.');
                }
        );
        $this->projectServiceClassExists = \Closure::fromCallable(
            $projectServiceClassExists ?? static fn(string $className): bool => class_exists($className)
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
        $sessionDependencies = $this->sessionDependencies($container);
        $config = $sessionDependencies->config();
        if ($group === null) {
            throw $this->createFatalException($sessionDependencies, 'Unset group name for \fan\core\service\session.');
        }
        if ($nameSpace === null) {
            $sessionConfig = $config->get('session');
            $group = 'app';
            $nameSpace = $sessionDependencies->application()->getAppName();
            $replacementName = $sessionConfig->get(['REPLACE_APP', $nameSpace]);
            if ($replacementName) {
                $nameSpace = (string)$replacementName;
            }
        }

        $nameSpace = (string)$nameSpace;
        $group = (string)$group;
        $instance = $sessionState->getInstance($group, $nameSpace);
        if ($instance === null) {
            $className = self::getProjectServiceClassName('session');
            if (!$this->projectServiceClassExists($className)) {
                throw new \InvalidArgumentException('Service "session" does not expose a project class.');
            }

            $instance = $sessionServiceFactory(
                $className,
                $nameSpace,
                $group,
                $config->get('database'),
                $sessionDependencies->requestInput(),
                $sessionDependencies->errorFactory(),
                $sessionDependencies->request(),
                null,
                $sessionDependencies->sessionFactory(),
                $sessionDependencies->dateFactory(),
                $sessionDependencies->cookieFactory(),
                $sessionDependencies->pearHttpSessionLoader(),
                $sessionEngineFactory,
                $sessionState,
                $sessionDependencies->bootstrapRuntime(),
                $config,
                $sessionDependencies->cacheFactory(),
                $sessionDependencies->phpRuntimeSettings(),
                $sessionDependencies->nativeSession(),
                $sessionDependencies->arrayValueReader()
            );
        }

        return $instance;
    }

    private function createFatalException(application_session_service_dependencies $sessionDependencies, string $message): \Throwable
    {
        $exception = ($this->fatalExceptionFactory)(
            $message,
            requestInput: $sessionDependencies->requestInput(),
            exceptionHeaderWriter: $sessionDependencies->headerWriter()
        );
        if (!$exception instanceof \Throwable) {
            throw new \UnexpectedValueException('Fatal exception factory must return a throwable object.');
        }

        return $exception;
    }

    private function sessionDependencies(container_interface $container): application_session_service_dependencies
    {
        return new application_session_service_dependencies($container);
    }

    private static function getProjectServiceClassName(string $serviceName): string
    {
        return '\fan\project\service\\' . trim($serviceName, " \t\n\r\0\x0B\\");
    }

    private function projectServiceClassExists(string $className): bool
    {
        return ($this->projectServiceClassExists)($className);
    }
}
