<?php

declare(strict_types=1);

namespace fan\core\bootstrap;

final class context_factory
{
    private \Closure $defaultFactoriesFactory;

    public function __construct(callable $defaultFactoriesFactory)
    {
        $this->defaultFactoriesFactory = \Closure::fromCallable($defaultFactoriesFactory);
    }

    public function __invoke(): context
    {
        return new context(defaultFactoriesFactory: $this->defaultFactoriesFactory);
    }

}
