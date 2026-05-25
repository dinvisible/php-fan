<?php

declare(strict_types=1);

namespace fan\core\bootstrap;

final class request_runner
{
    private \Closure $applicationFactory;

    private \Closure $errorHandlerFactory;

    public function __construct(callable $applicationFactory, callable $errorHandlerFactory)
    {
        $this->applicationFactory = \Closure::fromCallable($applicationFactory);
        $this->errorHandlerFactory = \Closure::fromCallable($errorHandlerFactory);
    }

    public function run(?string $configPath, bool $isEcho = true, ?object $application = null): mixed
    {
        $application ??= ($this->applicationFactory)();
        if (!is_object($application) || !method_exists($application, 'run') || !method_exists($application, 'context')) {
            throw new \RuntimeException('Request runner requires an application-like object.');
        }
        $errorHandler = ($this->errorHandlerFactory)($application);
        if (!is_callable($errorHandler)) {
            throw new \RuntimeException('Request runner error handler factory must return a callable.');
        }

        return $application->run($configPath, $isEcho, $errorHandler);
    }
}
