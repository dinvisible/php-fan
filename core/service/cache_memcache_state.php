<?php

declare(strict_types=1);

namespace fan\core\service;

final class cache_memcache_state
{
    private array $keepers = [];

    public function getKeeper(string $type): ?object
    {
        return $this->keepers[$type] ?? null;
    }

    public function setKeeper(string $type, object $keeper): void
    {
        $this->keepers[$type] = $keeper;
    }

    public function clear(): void
    {
        $this->keepers = [];
    }
}
