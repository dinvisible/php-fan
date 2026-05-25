<?php

declare(strict_types=1);

namespace fan\core\service;

final class tab_view_factories
{
    public function __construct(
        public readonly mixed $delegateFactory = null,
        public readonly mixed $viewParserFactory = null,
        public readonly mixed $blockFactory = null,
        public readonly mixed $blockExceptionFactory = null,
        public readonly mixed $metaRowFactory = null,
        public readonly mixed $uploadSizeLimitProvider = null,
        public readonly mixed $viewDefinerFactory = null,
        public readonly mixed $metaMakerFactory = null,
        public readonly mixed $viewRouterFactory = null,
        public readonly mixed $viewLoaderStateFactory = null,
        public readonly mixed $metaMakerStateFactory = null
    ) {
    }
}
