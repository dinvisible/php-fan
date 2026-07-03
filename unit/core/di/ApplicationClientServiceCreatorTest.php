<?php

declare(strict_types=1);

use fan\core\di\application_client_service_creator;
use fan\core\di\container;
use PHPUnit\Framework\TestCase;
use fan\project\service\cookie;
use fan\project\service\curl;
use fan\project\service\rest;


final class ApplicationClientServiceCreatorTest extends TestCase
{
    public function testCurlCreatorPassesExplicitDependenciesToInjectedFactory(): void
    {
        $container = $this->containerWithClientDependencies();
        $state = new ApplicationClientCurlStateDouble();
        $received = [];

        $curl = (new application_client_service_creator())->createCurlService(
            $container,
            $state,
            static function (mixed ...$arguments) use (&$received): object {
                $received = $arguments;

                return (object)['service' => 'curl'];
            },
            'https://example.test/api',
            'primary'
        );

        $this->assertSame('curl', $curl->service);
        $this->assertSame('\\' . curl::class, $received[0] ?? null);
        $this->assertSame('https://example.test/api', $received[1] ?? null);
        $this->assertSame('primary', $received[2] ?? null);
        $this->assertSame($state, $received[3] ?? null);
        $this->assertSame($container->get('bootstrap_runtime'), $received[4] ?? null);
        $this->assertSame($container->get('config'), $received[5] ?? null);
        $this->assertSame('cache-key', ($received[6])('cache-key')->type);
        $this->assertSame($container->get('curl_adapter'), $received[7] ?? null);
        $this->assertSame($container->get('array_adducer'), $received[8] ?? null);
        $this->assertSame($container->get('array_value_reader'), $received[9] ?? null);
        $this->assertSame($curl, $state->getInstance('primary', 'https://example.test/api'));
    }

    public function testRestCreatorPassesExplicitDependenciesToInjectedFactory(): void
    {
        $container = $this->containerWithClientDependencies();
        $state = new ApplicationClientRestStateDouble();
        $received = [];

        $rest = (new application_client_service_creator())->createRestService(
            $container,
            $state,
            static function (mixed ...$arguments) use (&$received): object {
                $received = $arguments;

                return (object)['service' => 'rest'];
            }
        );

        $this->assertSame('rest', $rest->service);
        $this->assertSame('\\' . rest::class, $received[0] ?? null);
        $this->assertSame('main', $received[1] ?? null);
        $this->assertSame($container->get('json'), ($received[2])());
        $this->assertSame('https://example.test/resource', ($received[3])('https://example.test/resource')->url);
        $this->assertSame($container->get('error'), ($received[4])());
        $this->assertSame($container->get('bootstrap_runtime'), $received[5] ?? null);
        $this->assertSame($container->get('config'), $received[6] ?? null);
        $this->assertSame('cache-key', ($received[7])('cache-key')->type);
        $this->assertSame($rest, $state->getInstance('main'));
    }

    public function testCookieCreatorPassesExplicitDependenciesToInjectedFactory(): void
    {
        $container = $this->containerWithClientDependencies();
        $state = new ApplicationClientCookieStateDouble();
        $received = [];

        $cookie = (new application_client_service_creator())->createCookieService(
            $container,
            $state,
            static function (mixed ...$arguments) use (&$received): object {
                $received = $arguments;

                return (object)['service' => 'cookie'];
            }
        );

        $this->assertSame('cookie', $cookie->service);
        $this->assertSame('\\' . cookie::class, $received[0] ?? null);
        $this->assertSame('/app', $received[1] ?? null);
        $this->assertSame('example.test', $received[2] ?? null);
        $this->assertTrue($received[3] ?? false);
        $this->assertSame($container->get('request_input'), $received[4] ?? null);
        $this->assertSame($container->get('error'), ($received[5])());
        $this->assertSame('encoded', ($received[6])(['value' => true]));
        $this->assertSame('decoded:raw', ($received[7])('raw'));
        $this->assertTrue(($received[8])('php-fan-json:"raw"'));
        $this->assertSame($container->get('cookie_writer'), $received[9] ?? null);
        $this->assertSame($state, $received[10] ?? null);
        $this->assertSame($container->get('bootstrap_runtime'), $received[11] ?? null);
        $this->assertSame($container->get('config'), $received[12] ?? null);
        $this->assertSame('cache-key', ($received[13])('cache-key')->type);
        $this->assertSame($cookie, $state->getInstance('/app', 'example.test'));
    }

    public function testProjectServiceClassAvailabilityCheckIsInjected(): void
    {
        $checkedClasses = [];
        $creator = new application_client_service_creator(
            static function (string $className) use (&$checkedClasses): bool {
                $checkedClasses[] = $className;

                return false;
            }
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Service "curl" does not expose a project class.');

        try {
            $creator->createCurlService(
                $this->containerWithClientDependencies(),
                new ApplicationClientCurlStateDouble(),
                static fn(): object => new stdClass(),
                'https://example.test/api'
            );
        } finally {
            $this->assertSame(['\fan\project\service\curl'], $checkedClasses);
        }
    }

    public function testClientCreatorUsesDependencyBundle(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_client_service_creator.php');
        $bundleSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_client_service_dependencies.php');
        $runtimeSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_client_runtime_dependencies.php');
        $bootstrapRuntimeSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_client_bootstrap_runtime_dependencies.php');
        $configCacheRuntimeSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_client_config_cache_runtime_dependencies.php');
        $configRuntimeSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_client_config_runtime_dependencies.php');
        $cacheRuntimeSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_client_cache_runtime_dependencies.php');
        $payloadSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_client_payload_dependencies.php');
        $arrayPayloadSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_client_array_payload_dependencies.php');
        $arrayAdducerPayloadSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_client_array_adducer_payload_dependencies.php');
        $arrayValueReaderPayloadSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_client_array_value_reader_payload_dependencies.php');
        $requestPayloadSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_client_request_payload_dependencies.php');
        $serializationPayloadSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_client_serialization_payload_dependencies.php');
        $serializerOperationsPayloadSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_client_serializer_operations_payload_dependencies.php');
        $cookieWriterPayloadSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_client_cookie_writer_payload_dependencies.php');
        $transportSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_client_transport_dependencies.php');
        $curlTransportSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_client_curl_transport_dependencies.php');
        $curlAdapterTransportSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_client_curl_adapter_transport_dependencies.php');
        $curlFactoryTransportSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_client_curl_factory_transport_dependencies.php');
        $serializationTransportSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_client_serialization_transport_dependencies.php');
        $errorTransportSource = file_get_contents(dirname(__DIR__, 3) . '/core/di/application_client_error_transport_dependencies.php');

        $this->assertIsString($source);
        $this->assertIsString($bundleSource);
        $this->assertIsString($runtimeSource);
        $this->assertIsString($bootstrapRuntimeSource);
        $this->assertIsString($configCacheRuntimeSource);
        $this->assertIsString($configRuntimeSource);
        $this->assertIsString($cacheRuntimeSource);
        $this->assertIsString($payloadSource);
        $this->assertIsString($arrayPayloadSource);
        $this->assertIsString($arrayAdducerPayloadSource);
        $this->assertIsString($arrayValueReaderPayloadSource);
        $this->assertIsString($requestPayloadSource);
        $this->assertIsString($serializationPayloadSource);
        $this->assertIsString($serializerOperationsPayloadSource);
        $this->assertIsString($cookieWriterPayloadSource);
        $this->assertIsString($transportSource);
        $this->assertIsString($curlTransportSource);
        $this->assertIsString($curlAdapterTransportSource);
        $this->assertIsString($curlFactoryTransportSource);
        $this->assertIsString($serializationTransportSource);
        $this->assertIsString($errorTransportSource);
        $this->assertStringContainsString('private function clientDependencies(container_interface $container): application_client_service_dependencies', $source);
        $this->assertStringContainsString('return new application_client_service_dependencies($container);', $source);
        $this->assertStringContainsString('$clientDependencies = $this->clientDependencies($container);', $source);
        $this->assertStringContainsString('$clientDependencies->bootstrapRuntime()', $source);
        $this->assertStringContainsString('$clientDependencies->config()', $source);
        $this->assertStringContainsString('$clientDependencies->cacheFactory()', $source);
        $this->assertStringContainsString('$clientDependencies->serializerOperations()', $source);
        $this->assertStringContainsString('final class application_client_service_dependencies', $bundleSource);
        $this->assertStringContainsString('new application_client_runtime_dependencies($container)', $bundleSource);
        $this->assertStringContainsString('new application_client_transport_dependencies($container)', $bundleSource);
        $this->assertStringContainsString('new application_client_payload_dependencies($container)', $bundleSource);
        $this->assertStringContainsString('new application_client_bootstrap_runtime_dependencies($container)', $runtimeSource);
        $this->assertStringContainsString('new application_client_config_cache_runtime_dependencies($container)', $runtimeSource);
        $this->assertStringContainsString('return $this->bootstrap->bootstrapRuntime();', $runtimeSource);
        $this->assertStringContainsString('return $this->configCache->cacheFactory();', $runtimeSource);
        $this->assertStringContainsString('return $this->container->get(service_id::BOOTSTRAP_RUNTIME);', $bootstrapRuntimeSource);
        $this->assertStringContainsString('new application_client_config_runtime_dependencies($container)', $configCacheRuntimeSource);
        $this->assertStringContainsString('new application_client_cache_runtime_dependencies($container)', $configCacheRuntimeSource);
        $this->assertStringContainsString('return $this->config->config();', $configCacheRuntimeSource);
        $this->assertStringContainsString('return $this->cache->cacheFactory();', $configCacheRuntimeSource);
        $this->assertStringContainsString('return $this->container->get(service_id::CONFIG);', $configRuntimeSource);
        $this->assertStringContainsString('return fn(string $type): mixed => $this->container->get(service_id::CACHE, $type);', $cacheRuntimeSource);
        $this->assertStringContainsString('new application_client_array_payload_dependencies($container)', $payloadSource);
        $this->assertStringContainsString('new application_client_request_payload_dependencies($container)', $payloadSource);
        $this->assertStringContainsString('new application_client_serialization_payload_dependencies($container)', $payloadSource);
        $this->assertStringContainsString('new application_client_array_adducer_payload_dependencies($container)', $arrayPayloadSource);
        $this->assertStringContainsString('new application_client_array_value_reader_payload_dependencies($container)', $arrayPayloadSource);
        $this->assertStringContainsString('return $this->arrayAdducer->arrayAdducer();', $arrayPayloadSource);
        $this->assertStringContainsString('return $this->arrayValueReader->arrayValueReader();', $arrayPayloadSource);
        $this->assertStringContainsString('new application_client_serializer_operations_payload_dependencies($container)', $serializationPayloadSource);
        $this->assertStringContainsString('new application_client_cookie_writer_payload_dependencies($container)', $serializationPayloadSource);
        $this->assertStringContainsString('return $this->serializerOperations->serializerOperations();', $serializationPayloadSource);
        $this->assertStringContainsString('return $this->cookieWriter->cookieWriter();', $serializationPayloadSource);
        $this->assertStringContainsString('new application_client_curl_transport_dependencies($container)', $transportSource);
        $this->assertStringContainsString('new application_client_serialization_transport_dependencies($container)', $transportSource);
        $this->assertStringContainsString('new application_client_error_transport_dependencies($container)', $transportSource);
        $this->assertStringContainsString('return $this->curl->curlAdapter();', $transportSource);
        $this->assertStringContainsString('return $this->serialization->jsonFactory();', $transportSource);
        $this->assertStringContainsString('return $this->error->errorFactory();', $transportSource);
        $this->assertStringContainsString('new application_client_curl_adapter_transport_dependencies($container)', $curlTransportSource);
        $this->assertStringContainsString('new application_client_curl_factory_transport_dependencies($container)', $curlTransportSource);
        $this->assertStringContainsString('return $this->curlAdapter->curlAdapter();', $curlTransportSource);
        $this->assertStringContainsString('return $this->curlFactory->curlFactory();', $curlTransportSource);
        $this->assertStringContainsString('return $this->container->get(service_id::ARRAY_ADDUCER);', $arrayAdducerPayloadSource);
        $this->assertStringContainsString('return $this->container->get(service_id::ARRAY_VALUE_READER);', $arrayValueReaderPayloadSource);
        $this->assertStringContainsString('return $this->container->get(service_id::REQUEST_INPUT);', $requestPayloadSource);
        $this->assertStringContainsString('return $this->container->get(service_id::SERIALIZER_OPERATIONS);', $serializerOperationsPayloadSource);
        $this->assertStringContainsString('return $this->container->get(service_id::COOKIE_WRITER);', $cookieWriterPayloadSource);
        $this->assertStringContainsString('return $this->container->get(service_id::CURL_ADAPTER);', $curlAdapterTransportSource);
        $this->assertStringContainsString('return fn(string $url): mixed => $this->container->get(service_id::CURL, $url);', $curlFactoryTransportSource);
        $this->assertStringContainsString('return fn(): mixed => $this->container->get(service_id::JSON);', $serializationTransportSource);
        $this->assertStringContainsString('return fn(): mixed => $this->container->get(service_id::ERROR);', $errorTransportSource);
    }

    private function containerWithClientDependencies(): container
    {
        $container = new container();
        $container
            ->factory('bootstrap_runtime', static fn(): object => (object)['name' => 'runtime'])
            ->factory('config', static fn(): object => new ApplicationClientConfigDouble())
            ->factory('cache', static fn(container $container, string $type): object => (object)['type' => $type], false)
            ->factory('json', static fn(): object => (object)['name' => 'json'])
            ->factory('curl', static fn(container $container, string $url): object => (object)['url' => $url], false)
            ->factory('curl_adapter', static fn(): object => (object)['name' => 'curl-adapter'])
            ->factory('array_adducer', static fn(): callable => static fn(mixed $value): array => is_array($value) ? $value : [$value])
            ->factory('array_value_reader', static fn(): callable => static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default)
            ->factory('error', static fn(): object => (object)['name' => 'error'])
            ->factory('request_input', static fn(): object => (object)['name' => 'request_input'])
            ->factory('serializer_operations', static fn(): object => new ApplicationClientSerializerOperationsDouble())
            ->factory('cookie_writer', static fn(): object => (object)['name' => 'cookie_writer']);

        return $container;
    }
}

final class ApplicationClientCurlStateDouble
{
    private array $instances = [];

    public function getInstance(int|float|string $index, string $url): mixed
    {
        return $this->instances[$this->key($index, $url)] ?? null;
    }

    public function setInstance(int|float|string $index, string $url, mixed $instance): void
    {
        $this->instances[$this->key($index, $url)] = $instance;
    }

    private function key(int|float|string $index, string $url): string
    {
        return (string)$index . ':' . $url;
    }
}

final class ApplicationClientRestStateDouble
{
    private array $instances = [];

    public function resolveConnectionName(?string $connectionName, string $defaultConnection): string
    {
        return $connectionName ?: $defaultConnection;
    }

    public function getInstance(string $connectionName): mixed
    {
        return $this->instances[$connectionName] ?? null;
    }

    public function setInstance(string $connectionName, mixed $instance): void
    {
        $this->instances[$connectionName] = $instance;
    }
}

final class ApplicationClientCookieStateDouble
{
    private array $instances = [];

    public function getInstance(mixed $path, mixed $domain): mixed
    {
        return $this->instances[$this->key($path, $domain)] ?? null;
    }

    public function setInstance(mixed $path, mixed $domain, mixed $instance): void
    {
        $this->instances[$this->key($path, $domain)] = $instance;
    }

    private function key(mixed $path, mixed $domain): string
    {
        return (string)$path . ':' . (string)$domain;
    }
}

final class ApplicationClientConfigDouble
{
    public function get(string $section): mixed
    {
        if ($section === 'rest') {
            return ['DEFAULT_CONNECTION' => 'main'];
        }
        if ($section === 'cookie') {
            return new ApplicationClientCookieConfigDouble();
        }

        return null;
    }
}

final class ApplicationClientCookieConfigDouble
{
    public function get(string $key, mixed $default = null): mixed
    {
        return match ($key) {
            'DEFAULT_PATH' => '/app',
            'DEFAULT_DOMAIN' => 'example.test',
            default => $default,
        };
    }
}

final class ApplicationClientSerializerOperationsDouble
{
    public function jsonPayloadEncoder(): callable
    {
        return static fn(mixed $value): string => 'encoded';
    }

    public function externalPayloadDecoder(): callable
    {
        return static fn(string $value, mixed $default = null): string => 'decoded:' . $value;
    }

    public function externalPayloadChecker(): callable
    {
        return static fn(string $value): bool => str_starts_with($value, 'php-fan-json:');
    }
}
