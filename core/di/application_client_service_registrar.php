<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_client_service_registrar
{
    public function __construct(private ?\Closure $dependenciesFactory = null)
    {
    }

    public function register(
        container $container,
        application_service_graph_registration_context $context
    ): container {
        $clientServiceCreator = $context->clientServiceCreator;
        $cookieServiceFactory = $context->cookieServiceFactory;
        $curlServiceFactory = $context->curlServiceFactory;
        $restServiceFactory = $context->restServiceFactory;
        $dependenciesFactory = $this->dependenciesFactory();

        return $container
            ->factory(
                service_id::COOKIE,
                static fn(container_interface $container, mixed $path = null, mixed $domain = null, bool $secure = false): mixed => $clientServiceCreator->createCookieService(
                    $container,
                    $dependenciesFactory($container)->cookieState(),
                    $cookieServiceFactory,
                    $path,
                    $domain,
                    $secure
                ),
                false
            )
            ->factory(
                service_id::CURL,
                static fn(container_interface $container, string $url, int|float|string $index = 0): mixed => $clientServiceCreator->createCurlService(
                    $container,
                    $dependenciesFactory($container)->curlState(),
                    $curlServiceFactory,
                    $url,
                    $index
                ),
                false
            )
            ->factory(
                service_id::REST,
                static fn(container_interface $container, ?string $connectionName = null): mixed => $clientServiceCreator->createRestService(
                    $container,
                    $dependenciesFactory($container)->restState(),
                    $restServiceFactory,
                    $connectionName
                ),
                false
            );
    }

    private function dependenciesFactory(): \Closure
    {
        return $this->dependenciesFactory
            ?? static fn(container_interface $container): application_client_service_registrar_dependencies => new application_client_service_registrar_dependencies($container);
    }
}
