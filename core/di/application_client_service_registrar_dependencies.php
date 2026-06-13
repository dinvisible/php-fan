<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_client_service_registrar_dependencies
{
    private application_client_cookie_state_registrar_dependencies $cookie;
    private application_client_curl_state_registrar_dependencies $curl;
    private application_client_rest_state_registrar_dependencies $rest;

    public function __construct(container_interface $container)
    {
        $this->cookie = new application_client_cookie_state_registrar_dependencies($container);
        $this->curl = new application_client_curl_state_registrar_dependencies($container);
        $this->rest = new application_client_rest_state_registrar_dependencies($container);
    }

    public function cookieState(): object
    {
        return $this->cookie->cookieState();
    }

    public function curlState(): object
    {
        return $this->curl->curlState();
    }

    public function restState(): object
    {
        return $this->rest->restState();
    }
}
