<?php

declare(strict_types=1);

namespace fan\core\service;

final class pager_state
{
    private array $instances = [];

    public function hasInstances(): bool
    {
        return $this->instances !== [];
    }

    public function getInstance(string $blockName): ?object
    {
        return $this->instances[$blockName] ?? null;
    }

    public function setInstance(string $blockName, object $pager): void
    {
        $this->instances[$blockName] = $pager;
    }

    public function clear(): void
    {
        $this->instances = [];
    }
}
