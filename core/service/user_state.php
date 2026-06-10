<?php

declare(strict_types=1);

namespace fan\core\service;

final class user_state
{
    private array $instances = [];
    private ?array $currentUsers = null;
    private ?array $prioritySpace = null;
    private ?string $currentUserSpace = null;
    private ?object $session = null;

    public function getInstance(string $userSpace, int|string $instanceKey): ?object
    {
        return $this->instances[$userSpace][$instanceKey] ?? null;
    }

    public function setInstance(string $userSpace, int|string $instanceKey, object $user): void
    {
        $this->instances[$userSpace][$instanceKey] = $user;
    }

    public function getCurrentUsers(): ?array
    {
        return $this->currentUsers;
    }

    public function setCurrentUsers(array $currentUsers): void
    {
        $this->currentUsers = $currentUsers;
    }

    public function getPrioritySpace(): ?array
    {
        return $this->prioritySpace;
    }

    public function setPrioritySpace(array $prioritySpace): void
    {
        $this->prioritySpace = $prioritySpace;
    }

    public function getCurrentUserSpace(): ?string
    {
        return $this->currentUserSpace;
    }

    public function setCurrentUserSpace(?string $currentUserSpace): void
    {
        $this->currentUserSpace = $currentUserSpace;
    }

    public function getSession(): ?object
    {
        return $this->session;
    }

    public function setSession(object $session): void
    {
        $this->session = $session;
    }

    public function clear(): void
    {
        $this->instances = [];
        $this->currentUsers = null;
        $this->prioritySpace = null;
        $this->currentUserSpace = null;
        $this->session = null;
    }
}
