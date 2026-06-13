<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_controller_obfuscator_factory_handler_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function obfuscatorFactory(): callable
    {
        return fn(string $type): mixed => $this->container->get(service_id::OBFUSCATOR, $type);
    }
}
