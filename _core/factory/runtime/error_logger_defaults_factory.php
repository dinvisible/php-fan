<?php

declare(strict_types=1);

namespace fan\core\runtime;

final class error_logger_defaults_factory
{
    private \Closure $errorLoggerFactory;

    public function __construct(callable $errorLoggerFactory)
    {
        $this->errorLoggerFactory = \Closure::fromCallable($errorLoggerFactory);
    }

    public function errorLogger(): callable
    {
        return ($this->errorLoggerFactory)();
    }
}
