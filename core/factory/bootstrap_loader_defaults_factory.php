<?php

declare(strict_types=1);

namespace fan\core\di;

final class bootstrap_loader_defaults_factory
{
    private \Closure $fileStorageFactory;

    public function __construct(callable $fileStorageFactory)
    {
        $this->fileStorageFactory = \Closure::fromCallable($fileStorageFactory);
    }

    public function fileStorage(): object
    {
        return ($this->fileStorageFactory)();
    }
}
