<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_session_context_group
{
    public function __construct(private container_interface $container)
    {
    }

    public function dependencies(): array
    {
        return [
            'sessionFactory' => fn(string $nameSpace, string $group = 'block'): mixed => $this->container->get('session', $nameSpace, $group),
        ];
    }
}
