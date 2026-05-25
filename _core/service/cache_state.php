<?php

declare(strict_types=1);

namespace fan\core\service;

final class cache_state
{
    private array $instances = [];

    public function hasInstances(): bool
    {
        return $this->instances !== [];
    }

    public function getInstance(string $type): ?object
    {
        return $this->instances[$type] ?? null;
    }

    public function setInstance(string $type, object $cache): void
    {
        $this->instances[$type] = $cache;
    }

    public function clear(): void
    {
        $this->instances = [];
    }
}
