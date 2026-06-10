<?php

declare(strict_types=1);

namespace fan\core\di;

final class user_engine_factory
{
    private \Closure $configuredServiceFactory;

    public function __construct(
        private object $serializerOperations,
        callable $configuredServiceFactory,
        private mixed $arrayValueReader = null,
        private mixed $arrayAdducer = null,
        private mixed $classNameResolver = null
    )
    {
        $this->configuredServiceFactory = \Closure::fromCallable($configuredServiceFactory);
    }

    public function __invoke(string $engineClass, mixed $identifyer): object
    {
        return ($this->configuredServiceFactory)($engineClass, [
            $identifyer,
            $this->serializerOperations->phpSnapshotEncoder(),
            $this->serializerOperations->phpSnapshotDecoder(),
            $this->arrayValueReader ?? static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => \array_val($array, $key, $default),
            $this->arrayAdducer ?? static fn(mixed $value): array => \adduceToArray($value),
            $this->classNameResolver ?? static fn(object $object): string => \get_class_alt($object) ?? get_class($object)
        ]);
    }
}
