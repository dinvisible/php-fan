<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_client_service_factory_defaults_provider
{
    private \Closure $curlServiceFactoryFactory;
    private \Closure $restServiceFactoryFactory;
    private \Closure $cookieServiceFactoryFactory;

    public function __construct(
        callable $curlServiceFactoryFactory,
        callable $restServiceFactoryFactory,
        callable $cookieServiceFactoryFactory
    ) {
        $this->curlServiceFactoryFactory = \Closure::fromCallable($curlServiceFactoryFactory);
        $this->restServiceFactoryFactory = \Closure::fromCallable($restServiceFactoryFactory);
        $this->cookieServiceFactoryFactory = \Closure::fromCallable($cookieServiceFactoryFactory);
    }

    public function curlServiceFactory(): callable
    {
        return $this->curlServiceFactoryFactory;
    }

    public function restServiceFactory(): callable
    {
        return $this->restServiceFactoryFactory;
    }

    public function cookieServiceFactory(): callable
    {
        return $this->cookieServiceFactoryFactory;
    }
}
