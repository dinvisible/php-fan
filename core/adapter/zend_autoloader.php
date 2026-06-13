<?php

declare(strict_types=1);

namespace fan\core\adapter;

class zend_autoloader
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
            $classExists ?? static fn(string $className, bool $autoload = false): bool => class_exists($className, $autoload)
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

    public static function load(string $zendPath): void
    {
        (new self())->loadPath($zendPath);
    }

    public function loadPath(string $zendPath): void
    {
        if (($this->classExists)('Zend_Loader_Autoloader', false)) {
            return;
        }

        $loaderPath = rtrim($zendPath, '/\\') . '/Loader/Autoloader.php';
        if (!($this->isReadable)($loaderPath)) {
            throw new \RuntimeException('Zend autoloader is not available at "' . $loaderPath . '".');
        }

        ($this->fileLoader)($loaderPath);
    }
}
