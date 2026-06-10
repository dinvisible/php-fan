<?php

declare(strict_types=1);

namespace fan\core\service;

class service_single_state
{
    /**
     * @var array<string, object>
     */
    private array $instances = [];

    public function hasInstance(string $className): bool
    {
        return isset($this->instances[$className]);
    }

    public function setInstance(string $className, object $service): void
    {
        $this->instances[$className] = $service;
    }

    public function getInstance(string $className): ?object
    {
        return $this->instances[$className] ?? null;
    }

    /**
     * @return array<string, object>
     */
    public function instances(): array
    {
        return $this->instances;
    }

    public function clear(): void
    {
        $this->instances = [];
    }
}
