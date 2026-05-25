<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\base\meta\maker;
use fan\core\base\meta\maker_state;
use fan\core\block\base;
use fan\project\base\meta\maker as meta_maker;


final class meta_maker_factory
{
    private \Closure $delayedFactory;
    private \Closure $recursiveMerger;
    private \Closure $arrayAdducer;
    private \Closure $classNameResolver;

    public function __construct(
        ?callable $delayedFactory = null,
        ?callable $recursiveMerger = null,
        ?callable $arrayAdducer = null,
        ?callable $classNameResolver = null
    )
    {
        $this->delayedFactory = \Closure::fromCallable($delayedFactory ?? new delayed_meta_factory());
        $this->recursiveMerger = \Closure::fromCallable($recursiveMerger ?? static fn(mixed ...$values): mixed => \array_merge_recursive_alt(...$values));
        $this->arrayAdducer = \Closure::fromCallable($arrayAdducer ?? static fn(mixed $value): array => \adduceToArray($value));
        $this->classNameResolver = \Closure::fromCallable($classNameResolver ?? static fn(object $object): string => \get_class_alt($object) ?? get_class($object));
    }

    public function __invoke(
        base $block,
        object $reflector,
        maker_state $state,
        callable $phpArrayFileLoader,
        callable $rowFactory,
        object $fileStorage,
        ?callable $blockExceptionFactory = null
    ): maker {
        return new meta_maker(
            $block,
            $reflector,
            $state,
            $phpArrayFileLoader,
            $rowFactory,
            $this->delayedFactory,
            $blockExceptionFactory,
            $fileStorage,
            $this->recursiveMerger,
            $this->arrayAdducer,
            $this->classNameResolver
        );
    }
}
