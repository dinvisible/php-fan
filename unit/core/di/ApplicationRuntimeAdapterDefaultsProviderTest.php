<?php

declare(strict_types=1);

use fan\core\di\application_runtime_adapter_defaults_provider;
use PHPUnit\Framework\TestCase;
use fan\core\adapter\cookie_writer;
use fan\core\adapter\curl_adapter;
use fan\core\adapter\data_loader;
use fan\core\adapter\error_log_writer;
use fan\core\adapter\header_writer;
use fan\core\di\container_interface;


final class ApplicationRuntimeAdapterDefaultsProviderTest extends TestCase
{
    public function testProviderCreatesRuntimeAdapterDefaults(): void
    {
        $provider = self::provider();
        $dependencies = [
            'request_input' => new class {
                public function request(): array
                {
                    return [];
                }
            },
            'json' => new class {
                public function encode(array $payload): string
                {
                    return json_encode($payload, JSON_THROW_ON_ERROR);
                }
            },
            'header_writer' => new header_writer(),
        ];
        $container = self::containerWith($dependencies);

        $factories = [
            'dataLoaderFactory' => data_loader::class,
            'errorLogWriterFactory' => error_log_writer::class,
            'headerWriterFactory' => header_writer::class,
            'cookieWriterFactory' => cookie_writer::class,
            'curlAdapterFactory' => curl_adapter::class,
        ];

        foreach ($factories as $method => $expectedClass) {
            $factory = $provider->{$method}();

            $this->assertInstanceOf(\Closure::class, \Closure::fromCallable($factory));
            $this->assertInstanceOf($expectedClass, $factory($container));
        }
    }

    private static function provider(): application_runtime_adapter_defaults_provider
    {
        return new application_runtime_adapter_defaults_provider(
            static fn(container_interface $container): object => new data_loader(
                $container->get('request_input'),
                $container->get('json'),
                static fn(mixed $value): array => \adduceToArray($value),
                static fn(mixed ...$values): mixed => \array_merge_recursive_alt(...$values),
                $container->get('header_writer')
            ),
            static fn(container_interface $container): object => new error_log_writer(),
            static fn(container_interface $container): object => new header_writer(),
            static fn(container_interface $container): object => new cookie_writer(),
            static fn(container_interface $container): object => new curl_adapter()
        );
    }

    private static function containerWith(array $dependencies): container_interface
    {
        return new class($dependencies) implements container_interface {
            public function __construct(private array $dependencies)
            {
            }

            public function has(string $id): bool
            {
                return array_key_exists($id, $this->dependencies);
            }

            public function get(string $id, mixed ...$arguments): mixed
            {
                return $this->dependencies[$id] ?? null;
            }
        };
    }
}
