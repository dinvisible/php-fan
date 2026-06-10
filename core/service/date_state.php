<?php

declare(strict_types=1);

namespace fan\core\service;

final class date_state
{
    private ?object $globalConfig = null;
    private array $instances = [];

    public function getGlobalConfig(): ?object
    {
        return $this->globalConfig;
    }

    public function setGlobalConfig(object $config): void
    {
        $this->globalConfig = $config;
    }

    public function hasInstances(): bool
    {
        return $this->instances !== [];
    }

    public function getInstance(bool $isTime, string $timezone, string $format, string $key): ?object
    {
        return $this->instances[$this->timeKey($isTime)][$timezone][$format][$key] ?? null;
    }

    public function setInstance(bool $isTime, string $timezone, string $format, string $key, object $date): void
    {
        $this->instances[$this->timeKey($isTime)][$timezone][$format][$key] = $date;
    }

    public function clear(): void
    {
        $this->globalConfig = null;
        $this->instances = [];
    }

    private function timeKey(bool $isTime): int
    {
        return $isTime ? 1 : 0;
    }
}
