<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_client_service_creator
{
    public function createCurlService(
        container_interface $container,
        object $state,
        callable $curlServiceFactory,
        string $url,
        int|float|string $index = 0
    ): mixed {
        $instance = $state->getInstance($index, $url);
        if ($instance === null) {
            $className = self::getProjectServiceClassName('curl');
            if (!class_exists($className)) {
                throw new \InvalidArgumentException('Service "curl" does not expose a project class.');
            }

            $instance = $curlServiceFactory(
                $className,
                $url,
                $index,
                $state,
                $container->get('bootstrap_runtime'),
                $container->get('config'),
                static fn(string $type): mixed => $container->get('cache', $type),
                $container->get('curl_adapter'),
                $container->get('array_adducer'),
                $container->get('array_value_reader')
            );
            $state->setInstance($index, $url, $instance);
        }

        return $instance;
    }

    public function createRestService(
        container_interface $container,
        object $state,
        callable $restServiceFactory,
        ?string $connectionName = null
    ): mixed {
        $config = $container->get('config')->get('rest');
        $connectionName = $state->resolveConnectionName($connectionName, (string)($config['DEFAULT_CONNECTION'] ?? ''));
        $instance = $state->getInstance($connectionName);
        if ($instance === null) {
            $className = self::getProjectServiceClassName('rest');
            if (!class_exists($className)) {
                throw new \InvalidArgumentException('Service "rest" does not expose a project class.');
            }

            $instance = $restServiceFactory(
                $className,
                $connectionName,
                static fn(): mixed => $container->get('json'),
                static fn(string $url): mixed => $container->get('curl', $url),
                static fn(): mixed => $container->get('error'),
                $container->get('bootstrap_runtime'),
                $container->get('config'),
                static fn(string $type): mixed => $container->get('cache', $type)
            );
            $state->setInstance($connectionName, $instance);
        }

        return $instance;
    }

    public function createCookieService(
        container_interface $container,
        object $state,
        callable $cookieServiceFactory,
        mixed $path = null,
        mixed $domain = null,
        bool $secure = false
    ): mixed {
        $config = $container->get('config')->get('cookie');
        if ($path === null) {
            $path = $config->get('DEFAULT_PATH', '/');
        }
        if ($domain === null) {
            $domain = $config->get('DEFAULT_DOMAIN');
        }

        $instance = $state->getInstance($path, $domain);
        if ($instance === null) {
            $className = self::getProjectServiceClassName('cookie');
            if (!class_exists($className)) {
                throw new \InvalidArgumentException('Service "cookie" does not expose a project class.');
            }
            $serializerOperations = $container->get('serializer_operations');

            $instance = $cookieServiceFactory(
                $className,
                $path,
                $domain,
                !empty($secure),
                $container->get('request_input'),
                static fn(): mixed => $container->get('error'),
                $serializerOperations->jsonPayloadEncoder(),
                $serializerOperations->externalPayloadDecoder(),
                $serializerOperations->externalPayloadChecker(),
                $container->get('cookie_writer'),
                $state,
                $container->get('bootstrap_runtime'),
                $container->get('config'),
                static fn(string $type): mixed => $container->get('cache', $type)
            );
            $state->setInstance($path, $domain, $instance);
        }

        return $instance;
    }

    private static function getProjectServiceClassName(string $serviceName): string
    {
        return '\fan\project\service\\' . trim($serviceName, " \t\n\r\0\x0B\\");
    }
}
