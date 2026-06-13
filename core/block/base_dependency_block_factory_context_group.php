<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_block_factory_context_group
{
    private base_dependency_block_factory_group $factory;
    private base_dependency_block_exception_factory_group $exceptionFactory;

    public function __construct(container_interface $container)
    {
        $this->factory = new base_dependency_block_factory_group($container);
        $this->exceptionFactory = new base_dependency_block_exception_factory_group($container);
    }

    public function dependencies(): array
    {
        return array_merge(
            $this->factory->dependencies(),
            $this->exceptionFactory->dependencies()
        );
    }
}
