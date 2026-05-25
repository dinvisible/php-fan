<?php

declare(strict_types=1);

namespace fan\core\di;

final class context_support_defaults_factory
{
    private \Closure $contextSupportDefaultsFactory;

    public function __construct(callable $contextSupportDefaultsFactory)
    {
        $this->contextSupportDefaultsFactory = \Closure::fromCallable($contextSupportDefaultsFactory);
    }

    /**
     * @return array<string, callable>
     */
    public function __invoke(): array
    {
        return ($this->contextSupportDefaultsFactory)();
    }
}
