<?php

declare(strict_types=1);

namespace fan\core\adapter;

class compiled_template_loader_state
{
    /**
     * @var array<string, string>
     */
    private array $paths = [];

    private bool $registered = false;

    public function setPath(string $className, string $path): void
    {
        $this->paths[ltrim($className, '\\')] = $path;
    }

    public function getPath(string $className): ?string
    {
        return $this->paths[ltrim($className, '\\')] ?? null;
    }

    public function isRegistered(): bool
    {
        return $this->registered;
    }

    public function markRegistered(): void
    {
        $this->registered = true;
    }
}
