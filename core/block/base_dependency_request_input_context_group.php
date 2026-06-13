<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_request_input_context_group
{
    public function __construct(private container_interface $container)
    {
    }

    public function dependencies(): array
    {
        return [
            'requestInputFactory' => fn(): mixed => $this->container->get('request_input'),
        ];
    }
}
