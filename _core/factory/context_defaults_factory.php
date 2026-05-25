<?php

declare(strict_types=1);

namespace fan\core\di;

final class context_defaults_factory
{
    private \Closure $contextDefaultsFactory;

    public function __construct(?callable $contextDefaultsFactory = null)
    {
        $this->contextDefaultsFactory = \Closure::fromCallable(
            $contextDefaultsFactory
                ?? static function (): array {
                    $contextCoreDefaults = (new context_core_defaults_provider_factory())();
                    $coreDefaults = $contextCoreDefaults();
                    $contextErrorHandlingDefaults = (new context_error_handling_defaults_provider_factory())();
                    $errorHandlingDefaults = $contextErrorHandlingDefaults();
                    $contextSupportDefaults = (new context_support_defaults_provider_factory())();
                    $supportDefaults = $contextSupportDefaults();

                    return array_replace($coreDefaults, $supportDefaults, $errorHandlingDefaults);
                }
        );
    }

    /**
     * @return array<string, callable>
     */
    public function __invoke(): array
    {
        return ($this->contextDefaultsFactory)();
    }
}
