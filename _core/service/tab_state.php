<?php

declare(strict_types=1);

namespace fan\core\service;

final class tab_state
{
    private array $errorTransfers = [];

    public function isErrorTransferEmpty(): bool
    {
        return $this->errorTransfers === [];
    }

    public function lastErrorTransferCode(): ?int
    {
        if ($this->errorTransfers === []) {
            return null;
        }

        return (int)end($this->errorTransfers);
    }

    public function hasErrorTransferCode(int $code): bool
    {
        return in_array($code, $this->errorTransfers, true);
    }

    public function pushErrorTransferCode(int $code): void
    {
        $this->errorTransfers[] = $code;
    }

    public function clear(): void
    {
        $this->errorTransfers = [];
    }
}
