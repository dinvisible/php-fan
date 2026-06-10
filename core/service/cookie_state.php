<?php

declare(strict_types=1);

namespace fan\core\service;

final class cookie_state
{
    private array $instances = [];
    private ?array $data = null;

    public function hasInstances(): bool
    {
        return $this->instances !== [];
    }

    public function getInstance(mixed $path, mixed $domain): ?object
    {
        [$domainKey, $pathKey, $legacyPathKey] = $this->instanceKeys($path, $domain);

        return $this->instances[$domainKey][$pathKey][$legacyPathKey] ?? null;
    }

    public function setInstance(mixed $path, mixed $domain, object $cookie): void
    {
        [$domainKey, $pathKey, $legacyPathKey] = $this->instanceKeys($path, $domain);
        $this->instances[$domainKey][$pathKey][$legacyPathKey] = $cookie;
    }

    public function initializeData(array $data): void
    {
        if ($this->data === null) {
            $this->data = $data;
        }
    }

    public function hasData(string $name): bool
    {
        return isset($this->data[$name]);
    }

    public function getData(string $name): mixed
    {
        return $this->data[$name] ?? null;
    }

    public function getAllData(): array
    {
        return $this->data ?? [];
    }

    public function setData(string $name, mixed $value): void
    {
        $this->data ??= [];
        $this->data[$name] = $value;
    }

    public function deleteData(string $name): void
    {
        unset($this->data[$name]);
    }

    public function clear(): void
    {
        $this->instances = [];
        $this->data = null;
    }

    private function instanceKeys(mixed $path, mixed $domain): array
    {
        $domainKey = empty($domain) ? '' : (string)$domain;
        $pathKey = empty($path) ? '' : (string)$path;

        return [$domainKey, $pathKey, $pathKey];
    }
}
