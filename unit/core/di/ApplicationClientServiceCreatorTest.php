<?php

declare(strict_types=1);

use fan\core\di\application_client_service_creator;
use fan\core\di\container;
use PHPUnit\Framework\TestCase;
use fan\project\service\cookie;
use fan\project\service\curl;
use fan\project\service\rest;


final class ApplicationClientServiceCreatorTest extends TestCase
{    public function testCurlCreatorPassesExplicitDependenciesToInjectedFactory(): void
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
        $this->assertFalse($received[3] ?? true);
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
