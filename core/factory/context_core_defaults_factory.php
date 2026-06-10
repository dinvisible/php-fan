<?php

declare(strict_types=1);

namespace fan\core\di;

final class context_core_defaults_factory
{
    private \Closure $contextCoreDefaultsFactory;

    public function __construct(callable $contextCoreDefaultsFactory)
    {
        $this->contextCoreDefaultsFactory = \Closure::fromCallable($contextCoreDefaultsFactory);
    }

    /**
     * @return array<string, callable>
     */
    public function __invoke(): array
    {
        return ($this->contextCoreDefaultsFactory)();
    }
}
