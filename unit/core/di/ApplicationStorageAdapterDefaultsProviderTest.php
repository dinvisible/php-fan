<?php

declare(strict_types=1);

use fan\core\di\application_storage_adapter_defaults_provider;
use PHPUnit\Framework\TestCase;
use fan\core\adapter\block_file_storage;
use fan\core\adapter\cache_file_storage;
use fan\core\adapter\cache_source_file_metadata;
use fan\core\adapter\config_source_file_storage;
use fan\core\adapter\email_template_file_storage;
use fan\core\adapter\entity_description_file_storage;
use fan\core\adapter\entity_file_discovery;
use fan\core\adapter\error_demonstrator_file_storage;
use fan\core\adapter\error_demonstrator_loader;
use fan\core\adapter\error_file_storage;
use fan\core\adapter\file_data_storage;
use fan\core\adapter\file_system_storage;
use fan\core\adapter\image_source_file_storage;
use fan\core\adapter\log_file_storage;
use fan\core\adapter\matcher_route_file_storage;
use fan\core\adapter\meta_file_storage;
use fan\core\adapter\model_request_file_storage;
use fan\core\adapter\obfuscator_file_storage;
use fan\core\adapter\plain_file_storage;
use fan\core\adapter\project_tool_file_storage;
use fan\core\adapter\restore_password_log_storage;
use fan\core\adapter\root_html_file_storage;
use fan\core\adapter\soap_wsdl_file_storage;
use fan\core\adapter\tab_alias_file_storage;
use fan\core\adapter\template_file_storage;
use fan\core\adapter\translation_file_storage;
use fan\core\di\container_interface;


final class ApplicationStorageAdapterDefaultsProviderTest extends TestCase
{
    public function testProviderCreatesStorageAdapterObjectDefaults(): void
    {
        $provider = new application_storage_adapter_defaults_provider();

        $this->assertInstanceOf(cache_file_storage::class, $provider->cacheFileStorage());
        $this->assertInstanceOf(cache_source_file_metadata::class, $provider->cacheSourceFileMetadata());
        $this->assertInstanceOf(config_source_file_storage::class, $provider->configSourceFileStorage());
        $this->assertInstanceOf(template_file_storage::class, $provider->templateFileStorage());
        $this->assertInstanceOf(model_request_file_storage::class, $provider->modelRequestFileStorage());
    }

    public function testProviderCreatesStorageAdapterFactories(): void
    {
        $provider = new application_storage_adapter_defaults_provider();
        $container = $this->createStub(container_interface::class);
        $container->method('get')->willReturn(new error_demonstrator_file_storage());

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

    public function testProviderAcceptsInjectedStorageAdapterDefaults(): void
    {
        $container = $this->createStub(container_interface::class);
        $objectDefaults = [
            'cacheFileStorage' => (object)['name' => 'cache'],
            'cacheSourceFileMetadata' => (object)['name' => 'cache-source'],
            'configSourceFileStorage' => (object)['name' => 'config-source'],
            'templateFileStorage' => (object)['name' => 'template'],
            'modelRequestFileStorage' => (object)['name' => 'model-request'],
        ];
        $factoryDefaults = [
            'restorePasswordLogStorageFactory' => (object)['name' => 'restore-password'],
            'blockFileStorageFactory' => (object)['name' => 'block'],
            'metaFileStorageFactory' => (object)['name' => 'meta'],
            'tabAliasFileStorageFactory' => (object)['name' => 'tab-alias'],
            'soapWsdlFileStorageFactory' => (object)['name' => 'soap-wsdl'],
            'errorFileStorageFactory' => (object)['name' => 'error'],
            'errorDemonstratorFileStorageFactory' => (object)['name' => 'error-demonstrator'],
            'errorDemonstratorLoaderFactory' => (object)['name' => 'error-demonstrator-loader'],
            'logFileStorageFactory' => (object)['name' => 'log'],
            'obfuscatorFileStorageFactory' => (object)['name' => 'obfuscator'],
            'entityFileDiscoveryFactory' => (object)['name' => 'entity-discovery'],
            'entityDescriptionFileStorageFactory' => (object)['name' => 'entity-description'],
            'projectToolFileStorageFactory' => (object)['name' => 'project-tool'],
            'emailTemplateFileStorageFactory' => (object)['name' => 'email-template'],
            'rootHtmlFileStorageFactory' => (object)['name' => 'root-html'],
            'translationFileStorageFactory' => (object)['name' => 'translation'],
            'fileDataStorageFactory' => (object)['name' => 'file-data'],
            'imageSourceFileStorageFactory' => (object)['name' => 'image-source'],
            'plainFileStorageFactory' => (object)['name' => 'plain'],
            'matcherRouteFileStorageFactory' => (object)['name' => 'matcher-route'],
            'fileSystemStorageFactory' => (object)['name' => 'file-system'],
        ];
        $provider = new application_storage_adapter_defaults_provider(
            $objectDefaults['cacheFileStorage'],
            $objectDefaults['cacheSourceFileMetadata'],
            $objectDefaults['configSourceFileStorage'],
            $objectDefaults['templateFileStorage'],
            $objectDefaults['modelRequestFileStorage'],
            ...array_map(
                static fn(object $default): callable => static fn(container_interface $container): object => $default,
                $factoryDefaults
            )
        );

        foreach ($objectDefaults as $method => $expected) {
            $this->assertSame($expected, $provider->{$method}());
        }

        foreach ($factoryDefaults as $method => $expected) {
            $factory = $provider->{$method}();

            $this->assertInstanceOf(\Closure::class, \Closure::fromCallable($factory));
            $this->assertSame($expected, $factory($container));
        }
    }
}
