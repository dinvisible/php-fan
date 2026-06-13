<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_obfuscator_model_factory_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function obfuscatorFactory(): callable
    {
        return fn(mixed ...$arguments): mixed => $this->container->get(service_id::OBFUSCATOR, ...$arguments);
    }
}
