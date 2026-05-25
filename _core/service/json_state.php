<?php

declare(strict_types=1);

namespace fan\core\service;

final class json_state
{
    private array $instances = [];

    public function getInstance(bool $useBase64): ?object
    {
        return $this->instances[$this->instanceKey($useBase64)] ?? null;
    }

    public function setInstance(bool $useBase64, object $json): void
    {
        $this->instances[$this->instanceKey($useBase64)] = $json;
    }

    public function clear(): void
    {
        $this->instances = [];
    }

    private function instanceKey(bool $useBase64): int
    {
        return $useBase64 ? 1 : 0;
    }
}
