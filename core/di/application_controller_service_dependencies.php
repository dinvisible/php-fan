<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_controller_service_dependencies
{
    private application_controller_plain_dependencies $plain;
    private application_controller_handler_dependencies $handler;
    private application_controller_runtime_dependencies $runtime;

    public function __construct(container_interface $container)
    {
        $this->plain = new application_controller_plain_dependencies($container);
        $this->handler = new application_controller_handler_dependencies($container);
        $this->runtime = new application_controller_runtime_dependencies($container);
    }

    public function matcher(): object
    {
        return $this->plain->matcher();
    }

    public function plainConfigFactory(): callable
    {
        return $this->plain->plainConfigFactory();
    }

    public function header(): object
    {
        return $this->plain->header();
    }

    public function obfuscatorFactory(): callable
    {
        return $this->handler->obfuscatorFactory();
    }

    public function request(): object
    {
        return $this->handler->request();
    }

    public function plainFileContext(): object
    {
        return $this->handler->plainFileContext();
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
}
