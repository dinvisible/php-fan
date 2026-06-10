<?php

declare(strict_types=1);

namespace fan\core\runtime;

final class error_handling_defaults_factory
{
    private \Closure $bootstrapErrorHandlerSetupFactory;
    private \Closure $phpRuntimeSettingsFactory;
    private \Closure $errorHandlerRegistrarFactory;
    private \Closure $errorHandlerSetupFactory;
    private \Closure $errorLoggerFactory;

    public function __construct(
        callable $bootstrapErrorHandlerSetupFactory,
        callable $phpRuntimeSettingsFactory,
        callable $errorHandlerRegistrarFactory,
        callable $errorHandlerSetupFactory,
        callable $errorLoggerFactory
    ) {
        $this->bootstrapErrorHandlerSetupFactory = \Closure::fromCallable($bootstrapErrorHandlerSetupFactory);
        $this->phpRuntimeSettingsFactory = \Closure::fromCallable($phpRuntimeSettingsFactory);
        $this->errorHandlerRegistrarFactory = \Closure::fromCallable($errorHandlerRegistrarFactory);
        $this->errorHandlerSetupFactory = \Closure::fromCallable($errorHandlerSetupFactory);
        $this->errorLoggerFactory = \Closure::fromCallable($errorLoggerFactory);
    }

    /**
     * @return array<string, callable>
     */
    public function __invoke(): array
    {
        $phpRuntimeSettings = ($this->phpRuntimeSettingsFactory)();
        $errorHandlerRegistrar = ($this->errorHandlerRegistrarFactory)();

        return [
            'bootstrapErrorHandlerSetup' => ($this->bootstrapErrorHandlerSetupFactory)(),
            'phpRuntimeSettingsFactory' => static fn(): object => $phpRuntimeSettings,
            'errorHandlerRegistrar' => $errorHandlerRegistrar,
            'errorHandlerSetup' => ($this->errorHandlerSetupFactory)($phpRuntimeSettings, $errorHandlerRegistrar),
            'errorLogger' => ($this->errorLoggerFactory)(),
        ];
    }
}
