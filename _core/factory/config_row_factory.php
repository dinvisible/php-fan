<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\base\data;
use fan\core\service\config\row;


final class config_row_factory
{
    private \Closure $snapshotEncoder;
    private \Closure $snapshotDecoder;
    private \Closure $serviceExceptionFactoryProvider;
    private \Closure $shortClassNameResolver;

    public function __construct(object $serializerOperations, ?callable $serviceExceptionFactory = null, ?callable $shortClassNameResolver = null)
    {
        $this->snapshotEncoder = \Closure::fromCallable($serializerOperations->phpSnapshotEncoder());
        $this->snapshotDecoder = \Closure::fromCallable($serializerOperations->phpSnapshotDecoder());
        $this->shortClassNameResolver = \Closure::fromCallable($shortClassNameResolver ?? static fn(object|string $object): string => \get_class_name($object) ?? (is_object($object) ? get_class($object) : $object));
        $normalizedServiceExceptionFactory = null;
        if ($serviceExceptionFactory !== null) {
            $normalizedServiceExceptionFactory = \Closure::fromCallable($serviceExceptionFactory);
        }
        $this->serviceExceptionFactoryProvider = static fn(): ?callable => $normalizedServiceExceptionFactory;
    }

    public function __invoke(mixed $data): row
    {
        $serviceExceptionFactory = ($this->serviceExceptionFactoryProvider)();
        $subDataFactory = null;
        $subDataFactory = function (
            mixed $value,
            int|string|null $key,
            data $superior
        ) use (&$subDataFactory, $serviceExceptionFactory): row {
            return new row(
                $value,
                $key,
                $superior,
                null,
                $subDataFactory,
                $this->snapshotEncoder,
                $this->snapshotDecoder,
                $serviceExceptionFactory,
                $this->shortClassNameResolver
            );
        };

        return new row(
            $data,
            null,
            null,
            null,
            $subDataFactory,
            $this->snapshotEncoder,
            $this->snapshotDecoder,
            $serviceExceptionFactory,
            $this->shortClassNameResolver
        );
    }
}
