<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_client_service_registrar
{
    public function register(
        container $container,
        application_service_graph_registration_context $context
    ): container {
        $clientServiceCreator = $context->clientServiceCreator;
        $cookieServiceFactory = $context->cookieServiceFactory;
        $curlServiceFactory = $context->curlServiceFactory;
        $restServiceFactory = $context->restServiceFactory;

        return $container
            ->factory(
                service_id::COOKIE,
                static fn(container_interface $container, mixed $path = null, mixed $domain = null, bool $secure = false): mixed => $clientServiceCreator->createCookieService(
                    $container,
                    $container->get(service_id::COOKIE_STATE),
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
                    $container->get(service_id::CURL_STATE),
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
                    $container->get(service_id::REST_STATE),
                    $restServiceFactory,
                    $connectionName
                ),
                false
            );
    }
}
