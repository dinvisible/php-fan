<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_utility_service_factory_defaults_provider
{
    private \Closure $obfuscatorServiceFactoryFactory;
    private \Closure $imageModifyServiceFactoryFactory;
    private \Closure $soapServiceFactoryFactory;
    private \Closure $dateServiceFactoryFactory;

    public function __construct(
        callable $obfuscatorServiceFactoryFactory,
        callable $imageModifyServiceFactoryFactory,
        callable $soapServiceFactoryFactory,
        callable $dateServiceFactoryFactory
    ) {
        $this->obfuscatorServiceFactoryFactory = \Closure::fromCallable($obfuscatorServiceFactoryFactory);
        $this->imageModifyServiceFactoryFactory = \Closure::fromCallable($imageModifyServiceFactoryFactory);
        $this->soapServiceFactoryFactory = \Closure::fromCallable($soapServiceFactoryFactory);
        $this->dateServiceFactoryFactory = \Closure::fromCallable($dateServiceFactoryFactory);
    }

    public function obfuscatorServiceFactory(): callable
    {
        return $this->obfuscatorServiceFactoryFactory;
    }

    public function imageModifyServiceFactory(): callable
    {
        return $this->imageModifyServiceFactoryFactory;
    }

    public function soapServiceFactory(): callable
    {
        return $this->soapServiceFactoryFactory;
    }

    public function dateServiceFactory(): callable
    {
        return $this->dateServiceFactoryFactory;
    }
}
