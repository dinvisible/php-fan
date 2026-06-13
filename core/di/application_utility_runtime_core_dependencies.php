<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_utility_runtime_core_dependencies
{
    private application_utility_bootstrap_runtime_runtime_core_dependencies $bootstrapRuntime;
    private application_utility_php_runtime_settings_runtime_core_dependencies $phpRuntimeSettings;

    public function __construct(container_interface $container)
    {
        $this->bootstrapRuntime = new application_utility_bootstrap_runtime_runtime_core_dependencies($container);
        $this->phpRuntimeSettings = new application_utility_php_runtime_settings_runtime_core_dependencies($container);
    }

    public function bootstrapRuntime(): object
    {
        return $this->bootstrapRuntime->bootstrapRuntime();
    }

    public function phpRuntimeSettings(): object
    {
        return $this->phpRuntimeSettings->phpRuntimeSettings();
    }
}
