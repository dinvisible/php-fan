<?php

declare(strict_types=1);

namespace fan\core\service;

final class rest_state
{
    private array $instances = [];
    private ?string $defaultName = null;

    public function resolveConnectionName(?string $connectionName, string $configuredDefault): string
    {
        if ($this->defaultName === null) {
            $this->defaultName = $configuredDefault;
        }

        return empty($connectionName) ? $this->defaultName : $connectionName;
    }

    public function getDefaultName(): ?string
    {
        return $this->defaultName;
    }

    public function hasInstances(): bool
    {
        return $this->instances !== [];
    }

    public function getInstance(string $connectionName): ?object
    {
        return $this->instances[$connectionName] ?? null;
    }

    public function setInstance(string $connectionName, object $rest): void
    {
        $this->instances[$connectionName] = $rest;
    }

    public function clear(): void
    {
        $this->instances = [];
        $this->defaultName = null;
    }
}
