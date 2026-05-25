<?php

declare(strict_types=1);

namespace fan\core\runtime;

final class error_handler_setup
{
    private object $phpRuntimeSettings;

    private $errorHandlerRegistrar;

    public function __construct(object $phpRuntimeSettings, callable $errorHandlerRegistrar)
    {
        $this->phpRuntimeSettings = $phpRuntimeSettings;
        $this->errorHandlerRegistrar = $errorHandlerRegistrar;
    }

    public function __invoke(callable $handler, string $defaultTimezone = 'Europe/Helsinki'): void
    {
        if (!$this->phpRuntimeSettings->get('date.timezone')) {
            $this->phpRuntimeSettings->set('date.timezone', $defaultTimezone);
        }

        ($this->errorHandlerRegistrar)($handler);
    }
}
