<?php

declare(strict_types=1);

namespace fan\core\base\meta;

class maker_state
{
    /**
     * @var array<string, array>
     */
    private array $metaCache = [];

    public function hasBlockSource(string $class): bool
    {
        return array_key_exists($class, $this->metaCache);
    }

    public function getBlockSource(string $class): array
    {
        return $this->metaCache[$class] ?? [];
    }

    public function setBlockSource(string $class, array $source): void
    {
        $this->metaCache[$class] = $source;
    }

    /**
     * @return array<string, array>
     */
    public function blockSources(): array
    {
        return $this->metaCache;
    }

    public function clear(): void
    {
        $this->metaCache = [];
    }
}
