<?php

declare(strict_types=1);

namespace fan\core\adapter;

final class php_array_file_loader
{
    private \Closure $phpArrayFile;

    public function __construct(?callable $phpArrayFile = null)
    {
        $this->phpArrayFile = \Closure::fromCallable($phpArrayFile ?? new php_array_file());
    }

    public function __invoke(string $path, mixed $default = null, ?object $context = null): mixed
    {
        return $context === null ? ($this->phpArrayFile)($path, $default) : ($this->phpArrayFile)($path, $default, $context);
    }
}
