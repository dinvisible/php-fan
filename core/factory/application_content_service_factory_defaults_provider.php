<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_content_service_factory_defaults_provider
{
    private \Closure $translationServiceFactoryFactory;

    public function __construct(callable $translationServiceFactoryFactory)
    {
        $this->translationServiceFactoryFactory = \Closure::fromCallable($translationServiceFactoryFactory);
    }

    public function translationServiceFactory(): callable
    {
        return $this->translationServiceFactoryFactory;
    }
}
