<?php

declare(strict_types=1);

namespace fan\core\adapter;

final class bootstrap_loader_file_storage
{
    private \Closure $symbolExists;
    private \Closure $fileLoader;

    public function __construct(?callable $symbolExists = null, ?callable $fileLoader = null)
    {
        $this->symbolExists = \Closure::fromCallable(
            $symbolExists
                ?? static fn(string $name): bool => class_exists($name, false) || interface_exists($name, false) || trait_exists($name, false)
        );
        $this->fileLoader = \Closure::fromCallable($fileLoader ?? static function (string $path, int $way = 3): mixed {
            switch ($way) {
            case 0:
                return include      $path;
            case 1:
                return include_once $path;
            case 2:
                return require      $path;
            case 3:
                return require_once $path;
            }

            throw new \InvalidArgumentException('Set incorrect way "' . $way . '" for load file');
        });
    }

    public function exists(string $path): bool
    {
        return file_exists($path);
    }

    public function isReadable(string $path): bool
    {
        return is_readable($path);
    }

    public function isFile(string $path): bool
    {
        return is_file($path);
    }

    public function isDirectory(string $path): bool
    {
        return is_dir($path);
    }

    public function realPath(string $path): string|false
    {
        return realpath($path);
    }

    public function load(string $path, int $way = 3): mixed
    {
        return ($this->fileLoader)($path, $way);
    }

    /**
     * @return list<string>|false
     */
    public function scanDirectory(string $path): array|false
    {
        return scandir($path);
    }

    public function registerAutoload(mixed $function, bool $prepend = false): void
    {
        spl_autoload_register($function, true, $prepend);
    }

    public function unregisterAutoload(mixed $function): void
    {
        spl_autoload_unregister($function);
    }

    public function aliasClass(string $original, string $alias, int $cntAliasArg): void
    {
        if ($cntAliasArg > 2) {
            class_alias($original, $alias, false);
        } else {
            class_alias($original, $alias);
        }
    }

    public function symbolExists(string $name): bool
    {
        return ($this->symbolExists)($name);
    }
}
