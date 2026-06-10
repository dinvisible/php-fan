<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\base\meta\delayed;
use fan\project\base\meta\delayed as meta_delayed;


final class delayed_meta_factory
{
    public function __invoke(object|string $object, string $method, mixed $arguments): delayed
    {
        return new meta_delayed($object, $method, $arguments);
    }
}
