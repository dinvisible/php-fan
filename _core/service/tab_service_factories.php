<?php

declare(strict_types=1);

namespace fan\core\service;

final class tab_service_factories
{
    public function __construct(
        public readonly mixed $roleFactory = null,
        public readonly mixed $transferFactory = null,
        public readonly mixed $configFactory = null,
        public readonly mixed $headerFactory = null,
        public readonly mixed $applicationFactory = null,
        public readonly mixed $debugFactory = null,
        public readonly mixed $jsonFactory = null,
        public readonly mixed $dataLoaderFactory = null,
        public readonly mixed $templateFactory = null,
        public readonly mixed $errorFactory = null,
        public readonly mixed $logFactory = null,
        public readonly mixed $cookieFactory = null,
        public readonly mixed $reflectorFactory = null,
        public readonly mixed $entityFactory = null,
        public readonly mixed $formFactory = null,
        public readonly mixed $pagerFactory = null,
        public readonly mixed $obfuscatorFactory = null,
        public readonly mixed $imageModifyFactory = null,
        public readonly mixed $databaseFactory = null,
        public readonly mixed $userFactory = null,
        public readonly mixed $dateFactory = null,
        public readonly mixed $phpArrayFileLoader = null
    ) {
    }
}
