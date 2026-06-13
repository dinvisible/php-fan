<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_session_runtime_dependencies
{
    private application_session_native_runtime_dependencies $native;
    private application_session_bootstrap_runtime_dependencies $bootstrap;
    private application_session_array_runtime_dependencies $array;

    public function __construct(container_interface $container)
    {
        $this->native = new application_session_native_runtime_dependencies($container);
        $this->bootstrap = new application_session_bootstrap_runtime_dependencies($container);
        $this->array = new application_session_array_runtime_dependencies($container);
    }

    public function pearHttpSessionLoader(): mixed
    {
        return $this->native->pearHttpSessionLoader();
    }

    public function bootstrapRuntime(): mixed
    {
        return $this->bootstrap->bootstrapRuntime();
    }

    public function phpRuntimeSettings(): mixed
    {
        return $this->bootstrap->phpRuntimeSettings();
    }

    public function nativeSession(): mixed
    {
        return $this->native->nativeSession();
    }

    public function arrayValueReader(): callable
    {
        return $this->array->arrayValueReader();
    }
}
