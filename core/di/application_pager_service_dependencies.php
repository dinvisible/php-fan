<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_pager_service_dependencies
{
    private application_pager_context_dependencies $context;
    private application_pager_runtime_dependencies $runtime;
    private application_pager_exception_dependencies $exception;

    public function __construct(container_interface $container)
    {
        $this->context = new application_pager_context_dependencies($container);
        $this->runtime = new application_pager_runtime_dependencies($container);
        $this->exception = new application_pager_exception_dependencies($container);
    }

    public function entityFactory(): callable
    {
        return $this->context->entityFactory();
    }

    public function tab(): object
    {
        return $this->context->tab();
    }

    public function tabFactory(): callable
    {
        return $this->context->tabFactory();
    }

    public function requestFactory(): callable
    {
        return $this->context->requestFactory();
    }

    public function bootstrapRuntime(): object
    {
        return $this->runtime->bootstrapRuntime();
    }

    public function config(): object
    {
        return $this->runtime->config();
    }

    public function cacheFactory(): callable
    {
        return $this->runtime->cacheFactory();
    }

    public function error500ExceptionFactory(): mixed
    {
        return $this->exception->error500ExceptionFactory();
    }
}
