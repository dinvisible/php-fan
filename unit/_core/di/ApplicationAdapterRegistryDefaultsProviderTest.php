<?php

declare(strict_types=1);

use fan\core\di\application_adapter_registry_defaults_provider;
use PHPUnit\Framework\TestCase;
use fan\core\adapter\block_file_storage;
use fan\core\adapter\cache_file_storage;
use fan\core\adapter\cache_source_file_metadata;
use fan\core\adapter\compiled_template_loader;
use fan\core\adapter\compiled_template_loader_state;
use fan\core\adapter\config_source_file_storage;
use fan\core\adapter\cookie_writer;
use fan\core\adapter\curl_adapter;
use fan\core\adapter\data_loader;
use fan\core\adapter\email_template_file_storage;
use fan\core\adapter\entity_description_file_storage;
use fan\core\adapter\entity_file_discovery;
use fan\core\adapter\error_demonstrator_file_storage;
use fan\core\adapter\error_demonstrator_loader;
use fan\core\adapter\error_file_storage;
use fan\core\adapter\error_log_writer;
use fan\core\adapter\file_data_storage;
use fan\core\adapter\file_system_storage;
use fan\core\adapter\header_writer;
use fan\core\adapter\image_canvas_operations;
use fan\core\adapter\image_metadata_reader;
use fan\core\adapter\image_output_writer;
use fan\core\adapter\image_resource_factory;
use fan\core\adapter\image_source_file_storage;
use fan\core\adapter\log_file_storage;
use fan\core\adapter\matcher_route_file_storage;
use fan\core\adapter\meta_file_storage;
use fan\core\adapter\model_request_file_storage;
use fan\core\adapter\native_session;
use fan\core\adapter\obfuscator_file_storage;
use fan\core\adapter\pear_http_session;
use fan\core\adapter\pear_http_session_loader;
use fan\core\adapter\php_array_file_loader;
use fan\core\adapter\plain_file_storage;
use fan\core\adapter\project_tool_file_storage;
use fan\core\adapter\restore_password_log_storage;
use fan\core\adapter\root_html_file_storage;
use fan\core\adapter\safe_serializer_operations;
use fan\core\adapter\soap_wsdl_file_storage;
use fan\core\adapter\tab_alias_file_storage;
use fan\core\adapter\template_file_storage;
use fan\core\adapter\translation_file_storage;
use fan\core\adapter\warning_capture;
use fan\core\di\application_compiled_template_adapter_defaults_provider;
use fan\core\di\application_core_adapter_defaults_provider;
use fan\core\di\application_image_adapter_defaults_provider;
use fan\core\di\application_runtime_adapter_defaults_provider;
use fan\core\di\application_session_adapter_defaults_provider;
use fan\core\di\application_storage_adapter_defaults_provider;
use fan\core\di\container_interface;


final class ApplicationAdapterRegistryDefaultsProviderTest extends TestCase
{
    public function testProviderCreatesAdapterRegistryConstructorDefaults(): void
    {
        $provider = self::defaultsProvider();

        $this->assertInstanceOf(warning_capture::class, $provider->warningCapture());
        $this->assertInstanceOf(cache_file_storage::class, $provider->cacheFileStorage());
        $this->assertInstanceOf(cache_source_file_metadata::class, $provider->cacheSourceFileMetadata());
        $this->assertInstanceOf(config_source_file_storage::class, $provider->configSourceFileStorage());
        $this->assertInstanceOf(native_session::class, $provider->nativeSession());
        $this->assertInstanceOf(pear_http_session::class, $provider->pearHttpSession());
        $this->assertInstanceOf(template_file_storage::class, $provider->templateFileStorage());
        $this->assertInstanceOf(model_request_file_storage::class, $provider->modelRequestFileStorage());
        $this->assertInstanceOf(php_array_file_loader::class, $provider->phpArrayFileLoader());

        $factory = $provider->serializerOperationsFactory();

        $this->assertInstanceOf(\Closure::class, \Closure::fromCallable($factory));
        $this->assertInstanceOf(
            safe_serializer_operations::class,
            $factory($provider->warningCapture())
        );
    }

    public function testProviderCreatesAdapterRegistryStorageFactories(): void
    {
        $provider = self::defaultsProvider();
        $container = self::containerWith([
            'error_demonstrator_file_storage' => new error_demonstrator_file_storage(),
        ]);

        $factories = [
            'restorePasswordLogStorageFactory' => restore_password_log_storage::class,
            'blockFileStorageFactory' => block_file_storage::class,
            'metaFileStorageFactory' => meta_file_storage::class,
            'tabAliasFileStorageFactory' => tab_alias_file_storage::class,
            'soapWsdlFileStorageFactory' => soap_wsdl_file_storage::class,
            'errorFileStorageFactory' => error_file_storage::class,
            'errorDemonstratorFileStorageFactory' => error_demonstrator_file_storage::class,
            'errorDemonstratorLoaderFactory' => error_demonstrator_loader::class,
            'logFileStorageFactory' => log_file_storage::class,
            'obfuscatorFileStorageFactory' => obfuscator_file_storage::class,
            'entityFileDiscoveryFactory' => entity_file_discovery::class,
            'entityDescriptionFileStorageFactory' => entity_description_file_storage::class,
            'projectToolFileStorageFactory' => project_tool_file_storage::class,
            'emailTemplateFileStorageFactory' => email_template_file_storage::class,
            'rootHtmlFileStorageFactory' => root_html_file_storage::class,
            'translationFileStorageFactory' => translation_file_storage::class,
            'fileDataStorageFactory' => file_data_storage::class,
            'imageSourceFileStorageFactory' => image_source_file_storage::class,
            'plainFileStorageFactory' => plain_file_storage::class,
            'matcherRouteFileStorageFactory' => matcher_route_file_storage::class,
            'fileSystemStorageFactory' => file_system_storage::class,
        ];

        foreach ($factories as $method => $expectedClass) {
            $factory = $provider->{$method}();

            $this->assertInstanceOf(\Closure::class, \Closure::fromCallable($factory));
            $this->assertInstanceOf($expectedClass, $factory($container));
        }
    }

    public function testProviderCreatesAdapterRegistrySessionAndTemplateFactories(): void
    {
        $provider = self::defaultsProvider();
        $state = new compiled_template_loader_state();
        $container = self::containerWith(['compiled_template_loader_state' => $state]);

        $factories = [
            'pearHttpSessionLoaderFactory' => pear_http_session_loader::class,
            'compiledTemplateLoaderStateFactory' => compiled_template_loader_state::class,
            'compiledTemplateLoaderFactory' => compiled_template_loader::class,
        ];

        foreach ($factories as $method => $expectedClass) {
            $factory = $provider->{$method}();

            $this->assertInstanceOf(\Closure::class, \Closure::fromCallable($factory));
            $this->assertInstanceOf($expectedClass, $factory($container));
        }
    }

    public function testProviderCreatesAdapterRegistryImageFactories(): void
    {
        $provider = self::defaultsProvider();
        $warningCapture = new warning_capture();
        $container = self::containerWith(['warning_capture' => $warningCapture]);

        $factories = [
            'imageMetadataReaderFactory' => image_metadata_reader::class,
            'imageResourceFactoryFactory' => image_resource_factory::class,
            'imageCanvasOperationsFactory' => image_canvas_operations::class,
            'imageOutputWriterFactory' => image_output_writer::class,
        ];

        foreach ($factories as $method => $expectedClass) {
            $factory = $provider->{$method}();

            $this->assertInstanceOf(\Closure::class, \Closure::fromCallable($factory));
            $this->assertInstanceOf($expectedClass, $factory($container));
        }
    }

    public function testProviderCreatesAdapterRegistryNetworkAndDataFactories(): void
    {
        $provider = self::defaultsProvider();
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

    private static function defaultsProvider(): application_adapter_registry_defaults_provider
    {
        return new application_adapter_registry_defaults_provider(
            self::coreAdapterDefaultsProvider(),
            new application_storage_adapter_defaults_provider(),
            self::runtimeAdapterDefaultsProvider(),
            new application_compiled_template_adapter_defaults_provider(),
            new application_session_adapter_defaults_provider(
                new native_session(),
                new pear_http_session()
            ),
            new application_image_adapter_defaults_provider()
        );
    }

    private static function coreAdapterDefaultsProvider(): application_core_adapter_defaults_provider
    {
        return new application_core_adapter_defaults_provider(
            new warning_capture(),
            new php_array_file_loader(),
            static fn(object $warningCapture): object => new safe_serializer_operations($warningCapture)
        );
    }

    private static function runtimeAdapterDefaultsProvider(): application_runtime_adapter_defaults_provider
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
}
