<?php

declare(strict_types=1);

namespace fan\core\adapter;

final class reflection_class_factory
{
    public function create(object|string $object): \ReflectionClass
    {
        return new \ReflectionClass($object);
    }
}
