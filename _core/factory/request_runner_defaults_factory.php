<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\bootstrap\request_runner;

final class request_runner_defaults_factory
{
    private \Closure $requestRunnerFactory;

    public function __construct(?callable $requestRunnerFactory = null)
    {
        if ($requestRunnerFactory === null) {
            $applicationDefaults = (new bootstrap_application_defaults_factory())->applicationDefaults();
            $requestRunnerFactory = static fn(): request_runner => new request_runner(
                $applicationDefaults->applicationFactory(),
                $applicationDefaults->errorHandlerFactory()
            );
        }

        $this->requestRunnerFactory = \Closure::fromCallable($requestRunnerFactory);
    }

    public function __invoke(): request_runner
    {
        return ($this->requestRunnerFactory)();
    }
}
