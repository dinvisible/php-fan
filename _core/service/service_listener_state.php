<?php

declare(strict_types=1);

namespace fan\core\service;

class service_listener_state
{
    /**
     * @var array<string, array<string, callable[]>>
     */
    private array $listeners = [];

    public function subscribe(string $serviceName, string $eventName, callable $callBack): void
    {
        if (!isset($this->listeners[$serviceName][$eventName])) {
            $this->listeners[$serviceName][$eventName] = [];
        }

        $this->listeners[$serviceName][$eventName][] = $callBack;
    }

    /**
     * @return callable[]
     */
    public function listenersFor(string $serviceName, string $eventName): array
    {
        return $this->listeners[$serviceName][$eventName] ?? [];
    }

    /**
     * @return array<string, array<string, callable[]>>
     */
    public function listeners(): array
    {
        return $this->listeners;
    }

    public function clear(): void
    {
        $this->listeners = [];
    }
}
