<?php

declare(strict_types=1);

namespace fan\core\adapter;

class php_array_file
{
    private \Closure $isReadable;
    private \Closure $fileReader;

    public function __construct(?callable $isReadable = null, ?callable $fileReader = null)
    {
        $this->isReadable = \Closure::fromCallable(
            $isReadable ?? static fn(string $path): bool => is_readable($path)
        );
        $this->fileReader = \Closure::fromCallable(
            $fileReader ?? static function (string $path, ?object $context = null): mixed {
                if ($context === null) {
                    return include $path;
                }

                return (function (string $path): mixed {
                    return include $path;
                })->call($context, $path);
            }
        );
    }

    public function __invoke(string $path, mixed $default = null, ?object $context = null): mixed
    {
        return $this->loadFile($path, $default, $context);
    }

    /**
     * Loads a PHP file that returns structured data, usually an array.
     */
    public static function load(string $path, mixed $default = null, ?object $context = null): mixed
    {
        return (new self())->loadFile($path, $default, $context);
    }

    public function loadFile(string $path, mixed $default = null, ?object $context = null): mixed
    {
        if (!($this->isReadable)($path)) {
            return $default;
        }

        return $context === null ? ($this->fileReader)($path) : ($this->fileReader)($path, $context);
    }
}
