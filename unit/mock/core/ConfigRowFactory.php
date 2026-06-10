<?php

declare(strict_types=1);

namespace FanTest\core;
use fan\core\base\data;
use fan\core\service\config\row;


final class ConfigRowFactory
{
    public static function row(
        mixed $data,
        ?callable $snapshotEncoder = null,
        ?callable $snapshotDecoder = null,
        ?callable $serviceExceptionFactory = null,
        ?callable $shortClassNameResolver = null
    ): row {
        $shortClassNameResolver ??= self::shortClassNameResolver();
        $subDataFactory = null;
        $subDataFactory = function (
            mixed $value,
            int|string|null $key,
            data $superior
        ) use (
            &$subDataFactory,
            $snapshotEncoder,
            $snapshotDecoder,
            $serviceExceptionFactory,
            $shortClassNameResolver
        ): row {
            return new row(
                $value,
                $key,
                $superior,
                null,
                $subDataFactory,
                $snapshotEncoder,
                $snapshotDecoder,
                $serviceExceptionFactory,
                $shortClassNameResolver
            );
        };

        return new row(
            $data,
            null,
            null,
            null,
            $subDataFactory,
            $snapshotEncoder,
            $snapshotDecoder,
            $serviceExceptionFactory,
            $shortClassNameResolver
        );
    }

    private static function shortClassNameResolver(): callable
    {
        return static function (object|string $object): string {
            $className = is_object($object) ? get_class($object) : $object;
            $parts = explode('\\', $className);

            return (string)end($parts);
        };
    }
}
