<?php

declare(strict_types=1);

namespace fan\core\adapter;

class compiled_template_loader
{
    private compiled_template_loader_state $state;

    private \Closure $classExists;
    private \Closure $isReadable;
    private \Closure $fileLoader;

    public function __construct(
        compiled_template_loader_state $state,
        ?callable $classExists = null,
        ?callable $isReadable = null,
        ?callable $fileLoader = null
    ) {
        $this->state = $state;
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

    public function load(string $className, string $path): bool
    {
        $this->state->setPath($className, $path);
        $this->register();

        return ($this->classExists)($className, true);
    }

    private function register(): void
    {
        if ($this->state->isRegistered()) {
            return;
        }

        $state = $this->state;
        $isReadable = $this->isReadable;
        $fileLoader = $this->fileLoader;
        spl_autoload_register(static function (string $class) use ($state, $isReadable, $fileLoader): void {
            $path = $state->getPath($class);
            if (is_string($path) && $isReadable($path)) {
                $fileLoader($path);
            }
        });
        $state->markRegistered();
    }
}
