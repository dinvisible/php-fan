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
                'cookie',
                static fn(container_interface $container, mixed $path = null, mixed $domain = null, bool $secure = false): mixed => $clientServiceCreator->createCookieService(
                    $container,
                    $container->get('cookie_state'),
                    $cookieServiceFactory,
                    $path,
                    $domain,
                    $secure
                ),
                false
            )
            ->factory(
                'curl',
                static fn(container_interface $container, string $url, int|float|string $index = 0): mixed => $clientServiceCreator->createCurlService(
                    $container,
                    $container->get('curl_state'),
                    $curlServiceFactory,
                    $url,
                    $index
                ),
                false
            )
            ->factory(
                'rest',
                static fn(container_interface $container, ?string $connectionName = null): mixed => $clientServiceCreator->createRestService(
                    $container,
                    $container->get('rest_state'),
                    $restServiceFactory,
                    $connectionName
                ),
                false
            );
    }
}
