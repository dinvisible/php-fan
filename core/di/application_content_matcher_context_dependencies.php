<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_content_matcher_context_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function matcher(): object
    {
        return $this->container->get(service_id::MATCHER);
    }
}
