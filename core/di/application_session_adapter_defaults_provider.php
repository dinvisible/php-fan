<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\adapter\pear_http_session_loader;


final class application_session_adapter_defaults_provider
{
    private object $nativeSession;

    private object $pearHttpSession;

    private \Closure $pearHttpSessionLoaderFactory;

    public function __construct(
        object $nativeSession,
        object $pearHttpSession,
        ?callable $pearHttpSessionLoaderFactory = null
    )
    {
        $this->nativeSession = $nativeSession;
        $this->pearHttpSession = $pearHttpSession;
        $this->pearHttpSessionLoaderFactory = \Closure::fromCallable(
            $pearHttpSessionLoaderFactory
                ?? static fn(container_interface $container): object => new pear_http_session_loader()
        );
    }

    public function nativeSession(): object
    {
        return $this->nativeSession;
    }

    public function pearHttpSession(): object
    {
        return $this->pearHttpSession;
    }

    public function pearHttpSessionLoaderFactory(): callable
    {
        return $this->pearHttpSessionLoaderFactory;
    }
}
