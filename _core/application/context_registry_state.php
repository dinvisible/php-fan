<?php

declare(strict_types=1);

namespace fan\core\bootstrap;

final class context_registry_state
{
    private ?context $context;

    private \Closure $contextFactory;

    public function __construct(?context $context = null, ?callable $contextFactory = null)
    {
        if ($context === null && $contextFactory === null) {
            throw new \RuntimeException('Bootstrap context registry state requires a context or context factory.');
        }
        $this->context = $context;
        $this->contextFactory = \Closure::fromCallable(
            $contextFactory
                ?? static fn(): context => $context
        );
    }

    public function context(): context
    {
        if ($this->context === null) {
            $context = ($this->contextFactory)();
            if (!$context instanceof context) {
                throw new \RuntimeException('Bootstrap context factory must return a bootstrap context.');
            }
            $this->context = $context;
        }

        return $this->context;
    }
}
