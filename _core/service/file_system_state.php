<?php

declare(strict_types=1);

namespace fan\core\service;

final class file_system_state
{
    private array $instances = [];

    public function hasInstances(): bool
    {
        return $this->instances !== [];
    }

    public function getInstance(string $fullPath): ?object
    {
        return $this->instances[$fullPath] ?? null;
    }

    public function setInstance(string $fullPath, object $fileSystem): void
    {
        $this->instances[$fullPath] = $fileSystem;
    }

    public function clear(): void
    {
        $this->instances = [];
    }
}
