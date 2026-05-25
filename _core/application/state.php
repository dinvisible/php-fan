<?php

declare(strict_types=1);

namespace fan\core\bootstrap;

use fan\core\di\container_interface;

class state
{
    private bool $isInit = false;

    private bool $isCli = false;

    private array $config = [];

    private array $replacement = [];

    private string $logDir = '{CORE_DIR}/../logs/bootstrap_log/';

    private ?object $initializer = null;

    private ?object $loader = null;

    private ?object $runner = null;

    private ?container_interface $container = null;

    private ?string $pid = null;

    public function isInit(): bool
    {
        return $this->isInit;
    }

    public function markInitialized(): void
    {
        $this->isInit = true;
    }

    public function setCli(bool $isCli): void
    {
        $this->isCli = $isCli;
    }

    public function isCli(): bool
    {
        return $this->isCli;
    }

    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    public function config(): array
    {
        return $this->config;
    }

    public function configValue(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    public function setReplacement(array $replacement): void
    {
        $this->replacement = $replacement;
    }

    public function fillPlaceholder(string $path): string
    {
        foreach ($this->replacement as $key => $value) {
            $path = str_replace((string)$key, (string)$value, $path);
        }

        return $path;
    }

    public function logDir(): string
    {
        return $this->logDir;
    }

    public function setLogDir(string $logDir): void
    {
        $this->logDir = $logDir;
    }

    public function initializer(): ?object
    {
        return $this->initializer;
    }

    public function setInitializer(object $initializer): void
    {
        $this->initializer = $initializer;
    }

    public function loader(): ?object
    {
        return $this->loader;
    }

    public function setLoader(object $loader): void
    {
        $this->loader = $loader;
    }

    public function runner(): ?object
    {
        return $this->runner;
    }

    public function setRunner(object $runner): void
    {
        $this->runner = $runner;
    }

    public function container(): ?container_interface
    {
        return $this->container;
    }

    public function setContainer(container_interface $container): void
    {
        $this->container = $container;
    }

    public function pid(): string
    {
        return $this->pid ??= uniqid();
    }
}
