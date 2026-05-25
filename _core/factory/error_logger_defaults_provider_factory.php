<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\runtime\error_logger;
use fan\core\runtime\error_logger_defaults_factory;
use fan\core\adapter\error_logger_file_storage;
use fan\core\adapter\error_log_writer;


final class error_logger_defaults_provider_factory
{
    private \Closure $errorLoggerFactoryProvider;

    public function __construct(?callable $errorLoggerFactoryProvider = null)
    {
        $this->errorLoggerFactoryProvider = \Closure::fromCallable(
            $errorLoggerFactoryProvider
                ?? static fn(): callable => new error_logger(
                    new error_log_writer(),
                    new error_logger_file_storage()
                )
        );
    }

    public function __invoke(): error_logger_defaults_factory
    {
        $errorLoggerFactoryProvider = $this->errorLoggerFactoryProvider;

        return new error_logger_defaults_factory(
            $errorLoggerFactoryProvider
        );
    }
}
