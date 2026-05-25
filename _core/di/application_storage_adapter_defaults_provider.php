<?php

declare(strict_types=1);

namespace fan\core\di;
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


final class application_storage_adapter_defaults_provider
{
    private object $cacheFileStorage;

    private object $cacheSourceFileMetadata;

    private object $configSourceFileStorage;

    private object $templateFileStorage;

    private object $modelRequestFileStorage;

    private \Closure $restorePasswordLogStorageFactory;

    private \Closure $blockFileStorageFactory;

    private \Closure $metaFileStorageFactory;

    private \Closure $tabAliasFileStorageFactory;

    private \Closure $soapWsdlFileStorageFactory;

    private \Closure $errorFileStorageFactory;

    private \Closure $errorDemonstratorFileStorageFactory;

    private \Closure $errorDemonstratorLoaderFactory;

    private \Closure $logFileStorageFactory;

    private \Closure $obfuscatorFileStorageFactory;

    private \Closure $entityFileDiscoveryFactory;

    private \Closure $entityDescriptionFileStorageFactory;

    private \Closure $projectToolFileStorageFactory;

    private \Closure $emailTemplateFileStorageFactory;

    private \Closure $rootHtmlFileStorageFactory;

    private \Closure $translationFileStorageFactory;

    private \Closure $fileDataStorageFactory;

    private \Closure $imageSourceFileStorageFactory;

    private \Closure $plainFileStorageFactory;

    private \Closure $matcherRouteFileStorageFactory;

    private \Closure $fileSystemStorageFactory;

    public function __construct(
        ?object $cacheFileStorage = null,
        ?object $cacheSourceFileMetadata = null,
        ?object $configSourceFileStorage = null,
        ?object $templateFileStorage = null,
        ?object $modelRequestFileStorage = null,
        ?callable $restorePasswordLogStorageFactory = null,
        ?callable $blockFileStorageFactory = null,
        ?callable $metaFileStorageFactory = null,
        ?callable $tabAliasFileStorageFactory = null,
        ?callable $soapWsdlFileStorageFactory = null,
        ?callable $errorFileStorageFactory = null,
        ?callable $errorDemonstratorFileStorageFactory = null,
        ?callable $errorDemonstratorLoaderFactory = null,
        ?callable $logFileStorageFactory = null,
        ?callable $obfuscatorFileStorageFactory = null,
        ?callable $entityFileDiscoveryFactory = null,
        ?callable $entityDescriptionFileStorageFactory = null,
        ?callable $projectToolFileStorageFactory = null,
        ?callable $emailTemplateFileStorageFactory = null,
        ?callable $rootHtmlFileStorageFactory = null,
        ?callable $translationFileStorageFactory = null,
        ?callable $fileDataStorageFactory = null,
        ?callable $imageSourceFileStorageFactory = null,
        ?callable $plainFileStorageFactory = null,
        ?callable $matcherRouteFileStorageFactory = null,
        ?callable $fileSystemStorageFactory = null
    )
    {
        $this->cacheFileStorage = $cacheFileStorage ?? new cache_file_storage();
        $this->cacheSourceFileMetadata = $cacheSourceFileMetadata ?? new cache_source_file_metadata();
        $this->configSourceFileStorage = $configSourceFileStorage ?? new config_source_file_storage();
        $this->templateFileStorage = $templateFileStorage ?? new template_file_storage();
        $this->modelRequestFileStorage = $modelRequestFileStorage ?? new model_request_file_storage();
        $this->restorePasswordLogStorageFactory = \Closure::fromCallable(
            $restorePasswordLogStorageFactory
                ?? static fn(container_interface $container): object => new restore_password_log_storage()
        );
        $this->blockFileStorageFactory = \Closure::fromCallable(
            $blockFileStorageFactory
                ?? static fn(container_interface $container): object => new block_file_storage()
        );
        $this->metaFileStorageFactory = \Closure::fromCallable(
            $metaFileStorageFactory
                ?? static fn(container_interface $container): object => new meta_file_storage()
        );
        $this->tabAliasFileStorageFactory = \Closure::fromCallable(
            $tabAliasFileStorageFactory
                ?? static fn(container_interface $container): object => new tab_alias_file_storage()
        );
        $this->soapWsdlFileStorageFactory = \Closure::fromCallable(
            $soapWsdlFileStorageFactory
                ?? static fn(container_interface $container): object => new soap_wsdl_file_storage()
        );
        $this->errorFileStorageFactory = \Closure::fromCallable(
            $errorFileStorageFactory
                ?? static fn(container_interface $container): object => new error_file_storage()
        );
        $this->errorDemonstratorFileStorageFactory = \Closure::fromCallable(
            $errorDemonstratorFileStorageFactory
                ?? static fn(container_interface $container): object => new error_demonstrator_file_storage()
        );
        $this->errorDemonstratorLoaderFactory = \Closure::fromCallable(
            $errorDemonstratorLoaderFactory
                ?? static fn(container_interface $container): object => new error_demonstrator_loader(
                    $container->get('error_demonstrator_file_storage')
                )
        );
        $this->logFileStorageFactory = \Closure::fromCallable(
            $logFileStorageFactory
                ?? static fn(container_interface $container): object => new log_file_storage()
        );
        $this->obfuscatorFileStorageFactory = \Closure::fromCallable(
            $obfuscatorFileStorageFactory
                ?? static fn(container_interface $container): object => new obfuscator_file_storage()
        );
        $this->entityFileDiscoveryFactory = \Closure::fromCallable(
            $entityFileDiscoveryFactory
                ?? static fn(container_interface $container): object => new entity_file_discovery()
        );
        $this->entityDescriptionFileStorageFactory = \Closure::fromCallable(
            $entityDescriptionFileStorageFactory
                ?? static fn(container_interface $container): object => new entity_description_file_storage()
        );
        $this->projectToolFileStorageFactory = \Closure::fromCallable(
            $projectToolFileStorageFactory
                ?? static fn(container_interface $container): object => new project_tool_file_storage()
        );
        $this->emailTemplateFileStorageFactory = \Closure::fromCallable(
            $emailTemplateFileStorageFactory
                ?? static fn(container_interface $container): object => new email_template_file_storage()
        );
        $this->rootHtmlFileStorageFactory = \Closure::fromCallable(
            $rootHtmlFileStorageFactory
                ?? static fn(container_interface $container): object => new root_html_file_storage()
        );
        $this->translationFileStorageFactory = \Closure::fromCallable(
            $translationFileStorageFactory
                ?? static fn(container_interface $container): object => new translation_file_storage()
        );
        $this->fileDataStorageFactory = \Closure::fromCallable(
            $fileDataStorageFactory
                ?? static fn(container_interface $container): object => new file_data_storage()
        );
        $this->imageSourceFileStorageFactory = \Closure::fromCallable(
            $imageSourceFileStorageFactory
                ?? static fn(container_interface $container): object => new image_source_file_storage()
        );
        $this->plainFileStorageFactory = \Closure::fromCallable(
            $plainFileStorageFactory
                ?? static fn(container_interface $container): object => new plain_file_storage()
        );
        $this->matcherRouteFileStorageFactory = \Closure::fromCallable(
            $matcherRouteFileStorageFactory
                ?? static fn(container_interface $container): object => new matcher_route_file_storage()
        );
        $this->fileSystemStorageFactory = \Closure::fromCallable(
            $fileSystemStorageFactory
                ?? static fn(container_interface $container): object => new file_system_storage()
        );
    }

    public function cacheFileStorage(): object
    {
        return $this->cacheFileStorage;
    }

    public function cacheSourceFileMetadata(): object
    {
        return $this->cacheSourceFileMetadata;
    }

    public function configSourceFileStorage(): object
    {
        return $this->configSourceFileStorage;
    }

    public function templateFileStorage(): object
    {
        return $this->templateFileStorage;
    }

    public function modelRequestFileStorage(): object
    {
        return $this->modelRequestFileStorage;
    }

    public function restorePasswordLogStorageFactory(): callable
    {
        return $this->restorePasswordLogStorageFactory;
    }

    public function blockFileStorageFactory(): callable
    {
        return $this->blockFileStorageFactory;
    }

    public function metaFileStorageFactory(): callable
    {
        return $this->metaFileStorageFactory;
    }

    public function tabAliasFileStorageFactory(): callable
    {
        return $this->tabAliasFileStorageFactory;
    }

    public function soapWsdlFileStorageFactory(): callable
    {
        return $this->soapWsdlFileStorageFactory;
    }

    public function errorFileStorageFactory(): callable
    {
        return $this->errorFileStorageFactory;
    }

    public function errorDemonstratorFileStorageFactory(): callable
    {
        return $this->errorDemonstratorFileStorageFactory;
    }

    public function errorDemonstratorLoaderFactory(): callable
    {
        return $this->errorDemonstratorLoaderFactory;
    }

    public function logFileStorageFactory(): callable
    {
        return $this->logFileStorageFactory;
    }

    public function obfuscatorFileStorageFactory(): callable
    {
        return $this->obfuscatorFileStorageFactory;
    }

    public function entityFileDiscoveryFactory(): callable
    {
        return $this->entityFileDiscoveryFactory;
    }

    public function entityDescriptionFileStorageFactory(): callable
    {
        return $this->entityDescriptionFileStorageFactory;
    }

    public function projectToolFileStorageFactory(): callable
    {
        return $this->projectToolFileStorageFactory;
    }

    public function emailTemplateFileStorageFactory(): callable
    {
        return $this->emailTemplateFileStorageFactory;
    }

    public function rootHtmlFileStorageFactory(): callable
    {
        return $this->rootHtmlFileStorageFactory;
    }

    public function translationFileStorageFactory(): callable
    {
        return $this->translationFileStorageFactory;
    }

    public function fileDataStorageFactory(): callable
    {
        return $this->fileDataStorageFactory;
    }

    public function imageSourceFileStorageFactory(): callable
    {
        return $this->imageSourceFileStorageFactory;
    }

    public function plainFileStorageFactory(): callable
    {
        return $this->plainFileStorageFactory;
    }

    public function matcherRouteFileStorageFactory(): callable
    {
        return $this->matcherRouteFileStorageFactory;
    }

    public function fileSystemStorageFactory(): callable
    {
        return $this->fileSystemStorageFactory;
    }
}
