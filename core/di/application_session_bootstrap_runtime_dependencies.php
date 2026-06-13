<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_session_bootstrap_runtime_dependencies
{
    private application_session_bootstrap_bootstrap_runtime_dependencies $bootstrapRuntime;
    private application_session_php_runtime_settings_bootstrap_runtime_dependencies $phpRuntimeSettings;

    public function __construct(container_interface $container)
    {
        $this->bootstrapRuntime = new application_session_bootstrap_bootstrap_runtime_dependencies($container);
        $this->phpRuntimeSettings = new application_session_php_runtime_settings_bootstrap_runtime_dependencies($container);
    }

    public function bootstrapRuntime(): mixed
    {
        return $this->bootstrapRuntime->bootstrapRuntime();
    }

    public function phpRuntimeSettings(): mixed
    {
        return $this->phpRuntimeSettings->phpRuntimeSettings();
    }
}
