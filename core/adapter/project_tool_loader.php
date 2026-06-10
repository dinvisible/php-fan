<?php

declare(strict_types=1);

namespace fan\core\adapter;

class project_tool_loader
{
    private \Closure $classExists;
    private \Closure $isReadable;
    private \Closure $fileLoader;

    public function __construct(
        ?callable $classExists = null,
        ?callable $isReadable = null,
        ?callable $fileLoader = null
    ) {
        $this->classExists = \Closure::fromCallable(
            $classExists ?? static fn(string $className, bool $autoload = true): bool => class_exists($className, $autoload)
        );
        $this->isReadable = \Closure::fromCallable(
            $isReadable ?? static fn(string $path): bool => is_readable($path)
        );
        $this->fileLoader = \Closure::fromCallable(
            $fileLoader ?? static function (string $path): void {
                require_once $path;
            }
        );
    }

    public function __invoke(string $className, string $path): bool
    {
        return $this->loadTool($className, $path);
    }

    public static function load(string $className, string $path): bool
    {
        return (new self())->loadTool($className, $path);
    }

    public function loadTool(string $className, string $path): bool
    {
        if (($this->classExists)($className, true)) {
            return true;
        }

        if (!($this->isReadable)($path)) {
            return false;
        }

        ($this->fileLoader)($path);

        return ($this->classExists)($className, false);
    }
}
