<?php

declare(strict_types=1);

namespace fan\core\block;

final class base_dependency_defaults
{
    public function dependencies(): array
    {
        return [
            'delayedMetaClassExists' => static fn(string $className): bool => class_exists($className, false),
            'blockExceptionClassExists' => static fn(string $class): bool => class_exists($class),
        ];
    }
}
