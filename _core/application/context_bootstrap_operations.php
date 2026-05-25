<?php

declare(strict_types=1);

namespace fan\core\bootstrap;

final class context_bootstrap_operations
{
    public function __construct(private context $context)
    {
    }

    public function getLoader(): loader
    {
        $loader = $this->state()->loader();
        if (!$loader instanceof loader) {
            throw new \RuntimeException('Bootstrap context loader is not initialized.');
        }

        return $loader;
    }

    public function getRunner(): runner
    {
        $runner = $this->state()->runner();
        if (!$runner instanceof runner) {
            throw new \RuntimeException('Bootstrap context runner is not initialized.');
        }

        return $runner;
    }

    public function getInitializer(): initializer
    {
        $initializer = $this->state()->initializer();
        if (!$initializer instanceof initializer) {
            throw new \RuntimeException('Bootstrap context initializer is not initialized.');
        }

        return $initializer;
    }

    public function parsePath(string $path): string
    {
        return $this->getLoader()->parsePath($path);
    }

    public function loadClass(string $class, bool $makeAlias = true): mixed
    {
        return $this->getLoader()->loadClass($class, $makeAlias);
    }

    public function logError(string $message): void
    {
        $this->context->logError($message);
    }

    public function handleError(
        int|float $errNo,
        string $errMsg,
        ?string $fileName = null,
        int|float|null $lineNum = null,
        mixed $errContext = null
    ): ?bool {
        if ($errNo === E_DEPRECATED || $errNo === E_USER_DEPRECATED) {
            return true;
        }
        $this->logError('Error No ' . $errNo . ': ' . $errMsg . ' in ' . $fileName . ' on line ' . $lineNum . '. Context: ' . var_export($errContext, true));

        return null;
    }

    public function getGlobalPath(string $key, mixed $altPath = null): ?string
    {
        $config = $this->state()->config();
        $paths = $config['bootstrap']['global_path'] ?? [];
        $path = empty($paths[$key]) ? $altPath : $paths[$key];

        return empty($path) ? null : $this->state()->fillPlaceholder((string)$path);
    }

    public function getConfigCache(): array
    {
        $config = $this->state()->config();

        return isset($config['config_cache']) ? $config['config_cache'] : [];
    }

    public function getPid(): string
    {
        return $this->state()->pid();
    }

    public function isCli(): bool
    {
        return $this->state()->isCli();
    }

    private function state(): state
    {
        return $this->context->state();
    }
}
