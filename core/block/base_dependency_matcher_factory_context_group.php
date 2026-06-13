<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_matcher_factory_context_group
{
    public function __construct(private container_interface $container)
    {
    }

    public function dependencies(): array
    {
        return [
            'matcherFactory' => fn(): mixed => $this->container->get('matcher'),
        ];
    }
}
