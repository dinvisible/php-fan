<?php

declare(strict_types=1);

namespace fan\core\adapter;

final class pear_http_session_loader
{
    /**
     * @var callable
     */
    private $loader;

    public function __construct(?callable $loader = null)
    {
        $this->loader = $loader ?? static fn(): mixed => pear_http_session::ensureAvailable();
    }

    public function load(): mixed
    {
        return ($this->loader)();
    }
}
