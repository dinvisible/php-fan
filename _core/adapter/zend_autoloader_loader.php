<?php

declare(strict_types=1);

namespace fan\core\adapter;

final class zend_autoloader_loader
{
    /**
     * @var callable
     */
    private $loader;

    public function __construct(?callable $loader = null)
    {
        $this->loader = $loader ?? static fn(string $zendPath): mixed => zend_autoloader::load($zendPath);
    }

    public function load(string $zendPath): mixed
    {
        return ($this->loader)($zendPath);
    }
}
