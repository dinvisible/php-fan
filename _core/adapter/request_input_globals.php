<?php

declare(strict_types=1);

namespace fan\core\adapter;

final class request_input_globals
{
    public function __construct(private object $environment)
    {
    }

    public function globalArray(string $name): array
    {
        return $this->environment->globalArray($name);
    }

    public function globalValue(string $name, mixed $default = null): mixed
    {
        return $this->environment->globalValue($name, $default);
    }

    public function unsetGlobalValue(string $name, mixed $key): void
    {
        $this->environment->unsetGlobalValue($name, $key);
    }

    public function serverValue(string $key, mixed $default = null): mixed
    {
        return $this->environment->serverValue($key, $default);
    }

    public function &sessionRoot(): array
    {
        $session =& $this->environment->sessionRoot();

        return $session;
    }

    public function apacheHeaders(): ?array
    {
        return $this->environment->apacheHeaders();
    }

    public function rawPost(): string
    {
        return $this->environment->rawPost();
    }
}
