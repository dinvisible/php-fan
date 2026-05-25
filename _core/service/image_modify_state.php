<?php

declare(strict_types=1);

namespace fan\core\service;

final class image_modify_state
{
    private array $instances = [];

    public function hasInstances(): bool
    {
        return $this->instances !== [];
    }

    public function getInstance(string $className): ?object
    {
        return $this->instances[$className] ?? null;
    }

    public function setInstance(string $className, object $image): void
    {
        $this->instances[$className] = $image;
    }

    public function clear(): void
    {
        $this->instances = [];
    }
}
