<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\adapter\compiled_template_loader;
use fan\core\adapter\compiled_template_loader_state;


final class application_compiled_template_adapter_defaults_provider
{
    private \Closure $compiledTemplateLoaderStateFactory;

    private \Closure $compiledTemplateLoaderFactory;

    public function __construct(
        ?callable $compiledTemplateLoaderStateFactory = null,
        ?callable $compiledTemplateLoaderFactory = null
    )
    {
        $this->compiledTemplateLoaderStateFactory = \Closure::fromCallable(
            $compiledTemplateLoaderStateFactory
                ?? static fn(container_interface $container): object => new compiled_template_loader_state()
        );
        $this->compiledTemplateLoaderFactory = \Closure::fromCallable(
            $compiledTemplateLoaderFactory
                ?? static fn(container_interface $container): object => new compiled_template_loader(
                    $container->get(service_id::COMPILED_TEMPLATE_LOADER_STATE)
                )
        );
    }

    public function compiledTemplateLoaderStateFactory(): callable
    {
        return $this->compiledTemplateLoaderStateFactory;
    }

    public function compiledTemplateLoaderFactory(): callable
    {
        return $this->compiledTemplateLoaderFactory;
    }
}
