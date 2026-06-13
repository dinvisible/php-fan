<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_utility_obfuscator_state_registrar_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function obfuscatorState(): object
    {
        return $this->container->get(service_id::OBFUSCATOR_STATE);
    }
}
