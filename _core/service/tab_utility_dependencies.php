<?php

declare(strict_types=1);

namespace fan\core\service;

final class tab_utility_dependencies
{
    public function __construct(
        public readonly mixed $arrayAdducer = null,
        public readonly mixed $recursiveMerger = null,
        public readonly mixed $arrayValueReader = null,
        public readonly mixed $arrayLikeChecker = null,
        public readonly mixed $shortClassNameResolver = null
    ) {
    }
}
