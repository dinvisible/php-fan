<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_content_service_dependencies
{
    private application_content_runtime_dependencies $runtime;
    private application_content_context_dependencies $context;
    private application_content_storage_dependencies $storage;

    public function __construct(container_interface $container)
    {
        $this->runtime = new application_content_runtime_dependencies($container);
        $this->context = new application_content_context_dependencies($container);
        $this->storage = new application_content_storage_dependencies($container);
    }

    public function locale(): object
    {
        return $this->context->locale();
    }

    public function bootstrapRuntime(): object
    {
        return $this->runtime->bootstrapRuntime();
    }

    public function tabFactory(): callable
    {
        return $this->context->tabFactory();
    }

    public function error(): object
    {
        return $this->context->error();
    }

    public function blockContext(): object
    {
        return $this->context->blockContext();
    }

    public function matcher(): object
    {
        return $this->context->matcher();
    }

    public function requestInput(): object
    {
        return $this->context->requestInput();
    }

    public function config(): object
    {
        return $this->runtime->config();
    }

    public function cacheFactory(): callable
    {
        return $this->runtime->cacheFactory();
    }

    public function phpArrayFileLoader(): object
    {
        return $this->storage->phpArrayFileLoader();
    }

    public function translationFileStorage(): object
    {
        return $this->storage->translationFileStorage();
    }
}
