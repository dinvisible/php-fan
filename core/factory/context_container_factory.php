<?php

declare(strict_types=1);

namespace fan\core\bootstrap;

use fan\core\di\container_interface;

final class context_container_factory
{
    private \Closure $applicationContainerFactory;

    public function __construct(callable $applicationContainerFactory)
    {
        $this->applicationContainerFactory = \Closure::fromCallable($applicationContainerFactory);
    }

    public function __invoke(?context $context = null): container_interface
    {
        $container = ($this->applicationContainerFactory)($context);
        if (!$container instanceof container_interface) {
            throw new \RuntimeException('Application container factory must return a container.');
        }

        return $container;
    }
}
