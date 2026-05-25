<?php

declare(strict_types=1);

namespace fan\core\service;

final class tab_storage_dependencies
{
    public function __construct(
        public readonly ?object $aliasFileStorage = null,
        public readonly ?object $imageMetadataReader = null,
        public readonly ?object $errorLogWriter = null,
        public readonly ?object $blockFileStorage = null,
        public readonly ?object $metaFileStorage = null,
        public readonly ?object $projectToolFileStorage = null,
        public readonly ?object $rootHtmlFileStorage = null
    ) {
    }
}
