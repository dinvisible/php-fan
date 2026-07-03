<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_client_service_creator
{
    private \Closure $projectServiceClassExists;

    public function __construct(?callable $projectServiceClassExists = null)
    {
        $this->projectServiceClassExists = \Closure::fromCallable(
            $projectServiceClassExists ?? static fn(string $className): bool => class_exists($className)
        );
    }

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
            if (!$this->projectServiceClassExists($className)) {
                throw new \InvalidArgumentException('Service "curl" does not expose a project class.');
            }
            $clientDependencies = $this->clientDependencies($container);

            $instance = $curlServiceFactory(
                $className,
                $url,
                $index,
                $state,
                $clientDependencies->bootstrapRuntime(),
                $clientDependencies->config(),
                $clientDependencies->cacheFactory(),
                $clientDependencies->curlAdapter(),
                $clientDependencies->arrayAdducer(),
                $clientDependencies->arrayValueReader()
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
        $clientDependencies = $this->clientDependencies($container);
        $config = $clientDependencies->config();
        $config = $config->get('rest');
        $connectionName = $state->resolveConnectionName($connectionName, (string)($config['DEFAULT_CONNECTION'] ?? ''));
        $instance = $state->getInstance($connectionName);
        if ($instance === null) {
            $className = self::getProjectServiceClassName('rest');
            if (!$this->projectServiceClassExists($className)) {
                throw new \InvalidArgumentException('Service "rest" does not expose a project class.');
            }

            $instance = $restServiceFactory(
                $className,
                $connectionName,
                $clientDependencies->jsonFactory(),
                $clientDependencies->curlFactory(),
                $clientDependencies->errorFactory(),
                $clientDependencies->bootstrapRuntime(),
                $clientDependencies->config(),
                $clientDependencies->cacheFactory()
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
        ?bool $secure = null
    ): mixed {
        $clientDependencies = $this->clientDependencies($container);
        $config = $clientDependencies->config();
        $config = $config->get('cookie');
        if ($path === null) {
            $path = $config->get('DEFAULT_PATH', '/');
        }
        if ($domain === null) {
            $domain = $config->get('DEFAULT_DOMAIN');
        }
        $secure ??= (bool)$config->get('DEFAULT_SECURE', true);
        $httpOnly = (bool)$config->get('DEFAULT_HTTP_ONLY', true);
        $sameSite = (string)$config->get('DEFAULT_SAME_SITE', 'Lax');

        $instance = $state->getInstance($path, $domain);
        if ($instance === null) {
            $className = self::getProjectServiceClassName('cookie');
            if (!$this->projectServiceClassExists($className)) {
                throw new \InvalidArgumentException('Service "cookie" does not expose a project class.');
            }
            $serializerOperations = $clientDependencies->serializerOperations();

            $instance = $cookieServiceFactory(
                $className,
                $path,
                $domain,
                !empty($secure),
                $clientDependencies->requestInput(),
                $clientDependencies->errorFactory(),
                $serializerOperations->jsonPayloadEncoder(),
                $serializerOperations->externalPayloadDecoder(),
                $serializerOperations->externalPayloadChecker(),
                $clientDependencies->cookieWriter(),
                $state,
                $clientDependencies->bootstrapRuntime(),
                $clientDependencies->config(),
                $clientDependencies->cacheFactory()
            );
            $state->setInstance($path, $domain, $instance);
        }
        if (method_exists($instance, 'setSecureFlag')) {
            $instance->setSecureFlag($secure);
        }
        if (method_exists($instance, 'setHttpOnlyFlag')) {
            $instance->setHttpOnlyFlag($httpOnly);
        }
        if (method_exists($instance, 'setSameSite')) {
            $instance->setSameSite($sameSite);
        }

        return $instance;
    }

    private function clientDependencies(container_interface $container): application_client_service_dependencies
    {
        return new application_client_service_dependencies($container);
    }

    private static function getProjectServiceClassName(string $serviceName): string
    {
        return '\fan\project\service\\' . trim($serviceName, " \t\n\r\0\x0B\\");
    }

    private function projectServiceClassExists(string $className): bool
    {
        return ($this->projectServiceClassExists)($className);
    }
}
