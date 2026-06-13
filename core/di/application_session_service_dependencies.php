<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_session_service_dependencies
{
    private application_session_context_dependencies $context;
    private application_session_factory_dependencies $factory;
    private application_session_runtime_dependencies $runtime;

    public function __construct(container_interface $container)
    {
        $this->context = new application_session_context_dependencies($container);
        $this->factory = new application_session_factory_dependencies($container);
        $this->runtime = new application_session_runtime_dependencies($container);
    }

    public function config(): mixed
    {
        return $this->context->config();
    }

    public function application(): mixed
    {
        return $this->context->application();
    }

    public function requestInput(): mixed
    {
        return $this->context->requestInput();
    }

    public function headerWriter(): mixed
    {
        return $this->context->headerWriter();
    }

    public function errorFactory(): callable
    {
        return $this->factory->errorFactory();
    }

    public function request(): mixed
    {
        return $this->context->request();
    }

    public function sessionFactory(): callable
    {
        return $this->factory->sessionFactory();
    }

    public function dateFactory(): callable
    {
        return $this->factory->dateFactory();
    }

    public function cookieFactory(): callable
    {
        return $this->factory->cookieFactory();
    }

    public function pearHttpSessionLoader(): mixed
    {
        return $this->runtime->pearHttpSessionLoader();
    }

    public function bootstrapRuntime(): mixed
    {
        return $this->runtime->bootstrapRuntime();
    }

    public function cacheFactory(): callable
    {
        return $this->factory->cacheFactory();
    }

    public function phpRuntimeSettings(): mixed
    {
        return $this->runtime->phpRuntimeSettings();
    }

    public function nativeSession(): mixed
    {
        return $this->runtime->nativeSession();
    }

    public function arrayValueReader(): callable
    {
        return $this->runtime->arrayValueReader();
    }
}
