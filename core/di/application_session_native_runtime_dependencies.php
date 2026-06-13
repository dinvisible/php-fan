<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_session_native_runtime_dependencies
{
    private application_session_pear_http_session_loader_native_runtime_dependencies $pearHttpSessionLoader;
    private application_session_native_session_native_runtime_dependencies $nativeSession;

    public function __construct(container_interface $container)
    {
        $this->pearHttpSessionLoader = new application_session_pear_http_session_loader_native_runtime_dependencies($container);
        $this->nativeSession = new application_session_native_session_native_runtime_dependencies($container);
    }

    public function pearHttpSessionLoader(): mixed
    {
        return $this->pearHttpSessionLoader->pearHttpSessionLoader();
    }

    public function nativeSession(): mixed
    {
        return $this->nativeSession->nativeSession();
    }
}
