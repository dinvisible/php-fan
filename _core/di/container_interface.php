<?php

declare(strict_types=1);

namespace fan\core\di;

interface container_interface
{
    public function has(string $id): bool;

    public function get(string $id, mixed ...$arguments): mixed;
}
