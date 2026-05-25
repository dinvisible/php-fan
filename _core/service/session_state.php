<?php

declare(strict_types=1);

namespace fan\core\service;

final class session_state
{
    private array $instances = [];
    private ?object $engine = null;
    private ?object $requestService = null;
    private ?bool $byCookie = null;
    private mixed $isExpired = false;
    private array $bufferData = [];
    private \Closure $arrayValueReader;

    public function __construct(callable $arrayValueReader)
    {
        $this->arrayValueReader = \Closure::fromCallable($arrayValueReader);
    }

    public function hasInstances(): bool
    {
        return $this->instances !== [];
    }

    public function getInstance(string $group, string $nameSpace): ?object
    {
        return $this->instances[$group][$nameSpace] ?? null;
    }

    public function setInstance(string $group, string $nameSpace, object $session): void
    {
        $this->instances[$group][$nameSpace] = $session;
    }

    public function clearInstances(): void
    {
        $this->instances = [];
    }

    public function getEngine(): ?object
    {
        return $this->engine;
    }

    public function setEngine(?object $engine): void
    {
        $this->engine = $engine;
    }

    public function getRequestService(): ?object
    {
        return $this->requestService;
    }

    public function setRequestService(?object $requestService): void
    {
        $this->requestService = $requestService;
    }

    public function isByCookie(): ?bool
    {
        return $this->byCookie;
    }

    public function setByCookie(?bool $byCookie): void
    {
        $this->byCookie = $byCookie;
    }

    public function isExpired(): bool
    {
        return (bool)$this->isExpired;
    }

    public function setExpired(mixed $isExpired): void
    {
        $this->isExpired = $isExpired;
    }

    public function setExpiredReference(mixed &$isExpired): void
    {
        $this->isExpired =& $isExpired;
    }

    public function setBufferData(string $key, mixed $value): void
    {
        $this->bufferData[$key] = $value;
    }

    public function getBufferData(string $key, mixed $default = null): mixed
    {
        return ($this->arrayValueReader)($this->bufferData, $key, $default);
    }

    public function clear(): void
    {
        $this->clearInstances();
        $this->engine = null;
        $this->requestService = null;
        $this->byCookie = null;
        $this->isExpired = false;
        $this->bufferData = [];
    }
}
