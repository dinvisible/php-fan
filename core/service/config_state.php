<?php

declare(strict_types=1);

namespace fan\core\service;

final class config_state
{
    private array $instances = [];
    private array $engines = [];
    private ?object $cache = null;
    private ?object $thisConfig = null;
    private array $appDepended = [];

    public function getInstance(string $configType): ?object
    {
        return $this->instances[$configType] ?? null;
    }

    public function setInstance(string $configType, object $config): void
    {
        $this->instances[$configType] = $config;
    }

    public function getEngine(?string $sourceType): ?object
    {
        return $this->engines[(string)$sourceType] ?? null;
    }

    public function setEngine(?string $sourceType, object $engine): void
    {
        $this->engines[(string)$sourceType] = $engine;
    }

    public function getCache(): ?object
    {
        return $this->cache;
    }

    public function setCache(?object $cache): void
    {
        $this->cache = $cache;
    }

    public function getThisConfig(): ?object
    {
        return $this->thisConfig;
    }

    public function setThisConfig(object $config): void
    {
        $this->thisConfig = $config;
    }

    public function getAppDepended(): array
    {
        return $this->appDepended;
    }

    public function setAppDepended(array $appDepended): void
    {
        $this->appDepended = $appDepended;
    }

    public function clear(): void
    {
        $this->instances = [];
        $this->engines = [];
        $this->cache = null;
        $this->thisConfig = null;
        $this->appDepended = [];
    }
}
