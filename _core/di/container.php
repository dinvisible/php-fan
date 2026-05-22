<?php

declare(strict_types=1);

namespace fan\core\di;

/**
 * Lightweight dependency container for framework services.
 *
 * The container keeps explicit service instances and factories in one place so
 * legacy static service access can be migrated incrementally to injected
 * dependencies.
 */
class container implements container_interface
{
    /**
     * @var array<string, callable>
     */
    private array $factories = [];

    /**
     * @var array<string, object>
     */
    private array $instances = [];

    /**
     * @var array<string, string>
     */
    private array $aliases = [];

    /**
     * @var array<string, bool>
     */
    private array $sharedFactories = [];

    /**
     * @param string $id Unique identifier used to locate the target item.
     */
    public function set(string $id, object $service): self
    {
        $id = $this->normalizeId($id);
        $this->instances[$id] = $service;

        return $this;
    }

    /**
     * @param string $id Unique identifier used to locate the target item.
     */
    public function factory(string $id, callable $factory, bool $shared = true): self
    {
        $id = $this->normalizeId($id);
        $this->factories[$id] = $factory;
        $this->sharedFactories[$id] = $shared;

        return $this;
    }

    /**
     * @param string $id Unique identifier used to locate the target item.
     */
    public function alias(string $alias, string $id): self
    {
        $this->aliases[$this->normalizeId($alias)] = $this->normalizeId($id);

        return $this;
    }

    /**
     * @param string $id Unique identifier used to locate the target item.
     */
    public function has(string $id): bool
    {
        $id = $this->resolveAlias($id);

        return isset($this->instances[$id]) || isset($this->factories[$id]);
    }

    /**
     * @param string $id Unique identifier used to locate the target item.
     */
    public function get(string $id, mixed ...$arguments): mixed
    {
        $id = $this->resolveAlias($id);

        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }
        if (!isset($this->factories[$id])) {
            throw new \InvalidArgumentException('Service "' . $id . '" is not registered in the container.');
        }

        $service = ($this->factories[$id])($this, ...$arguments);
        if ($arguments === [] && ($this->sharedFactories[$id] ?? true) && is_object($service)) {
            $this->instances[$id] = $service;
        }

        return $service;
    }

    /**
     * @param string $id Unique identifier used to locate the target item.
     */
    public function remove(string $id): self
    {
        $id = $this->normalizeId($id);
        unset($this->instances[$id], $this->factories[$id], $this->aliases[$id], $this->sharedFactories[$id]);

        return $this;
    }

    public function clear(): self
    {
        $this->factories = [];
        $this->instances = [];
        $this->aliases = [];
        $this->sharedFactories = [];

        return $this;
    }

    /**
     * @param string $id Unique identifier used to locate the target item.
     */
    private function normalizeId(string $id): string
    {
        return strtolower(trim($id, " \t\n\r\0\x0B\\"));
    }

    /**
     * @param string $id Unique identifier used to locate the target item.
     */
    private function resolveAlias(string $id): string
    {
        $id = $this->normalizeId($id);
        $visited = [];

        while (isset($this->aliases[$id])) {
            if (isset($visited[$id])) {
                throw new \LogicException('Circular service alias detected for "' . $id . '".');
            }
            $visited[$id] = true;
            $id = $this->aliases[$id];
        }

        return $id;
    }
}
