<?php

declare(strict_types=1);

namespace fan\core\service;

final class curl_state
{
    private array $instances = [];

    public function hasInstances(): bool
    {
        return $this->instances !== [];
    }

    public function getInstance(int|float|string $index, string $url): ?object
    {
        return $this->instances[$this->indexKey($index)][$url] ?? null;
    }

    public function setInstance(int|float|string $index, string $url, object $curl): void
    {
        $this->instances[$this->indexKey($index)][$url] = $curl;
    }

    public function removeInstance(int|float|string $index, string $url): void
    {
        $key = $this->indexKey($index);
        unset($this->instances[$key][$url]);
        if (($this->instances[$key] ?? []) === []) {
            unset($this->instances[$key]);
        }
    }

    public function clear(): void
    {
        $this->instances = [];
    }

    private function indexKey(int|float|string $index): string
    {
        return (string)$index;
    }
}
