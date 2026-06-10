<?php

declare(strict_types=1);

namespace fan\core\di;

final class context_container_defaults_factory
{
    private \Closure $contextContainerFactoryFactory;
    private \Closure $applicationContainerFactory;

    public function __construct(callable $contextContainerFactoryFactory, callable $applicationContainerFactory)
    {
        $this->contextContainerFactoryFactory = \Closure::fromCallable($contextContainerFactoryFactory);
        $this->applicationContainerFactory = \Closure::fromCallable($applicationContainerFactory);
    }

    public function __invoke(): callable
    {
        return ($this->contextContainerFactoryFactory)($this->applicationContainerFactory);
    }
}
