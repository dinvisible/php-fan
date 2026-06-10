<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_adapter_registry
{
    private application_adapter_registry_defaults_provider $defaultsProvider;

    private object $warningCapture;

    private object $cacheFileStorage;

    private object $cacheSourceFileMetadata;

    private object $configSourceFileStorage;

    private object $nativeSession;

    private object $pearHttpSession;

    private object $templateFileStorage;

    private object $modelRequestFileStorage;

    private object $phpArrayFileLoader;

    private \Closure $serializerOperationsFactory;

    public function __construct(application_adapter_registry_defaults_provider $defaultsProvider)
    {
        $this->defaultsProvider = $defaultsProvider;
        $this->warningCapture = $defaultsProvider->warningCapture();
        $this->cacheFileStorage = $defaultsProvider->cacheFileStorage();
        $this->cacheSourceFileMetadata = $defaultsProvider->cacheSourceFileMetadata();
        $this->configSourceFileStorage = $defaultsProvider->configSourceFileStorage();
        $this->nativeSession = $defaultsProvider->nativeSession();
        $this->pearHttpSession = $defaultsProvider->pearHttpSession();
        $this->templateFileStorage = $defaultsProvider->templateFileStorage();
        $this->modelRequestFileStorage = $defaultsProvider->modelRequestFileStorage();
        $this->phpArrayFileLoader = $defaultsProvider->phpArrayFileLoader();
        $this->serializerOperationsFactory = \Closure::fromCallable($defaultsProvider->serializerOperationsFactory());
    }

    public function warningCapture(): object
    {
        return $this->warningCapture;
    }

    public function cacheFileStorage(): object
    {
        return $this->cacheFileStorage;
    }

    public function nativeSession(): object
    {
        return $this->nativeSession;
    }

    public function pearHttpSession(): object
    {
        return $this->pearHttpSession;
    }

    public function modelRequestFileStorage(): object
    {
        return $this->modelRequestFileStorage;
    }

    public function phpArrayFileLoader(): callable
    {
        return $this->phpArrayFileLoader;
    }

    public function serializerOperationsFactory(): callable
    {
        return $this->serializerOperationsFactory;
    }

    public function register(container $container, callable $phpArrayFileLoader): container
    {
        $warningCapture = $this->warningCapture;
        $cacheFileStorage = $this->cacheFileStorage;
        $cacheSourceFileMetadata = $this->cacheSourceFileMetadata;
        $configSourceFileStorage = $this->configSourceFileStorage;
        $nativeSession = $this->nativeSession;
        $pearHttpSession = $this->pearHttpSession;
        $templateFileStorage = $this->templateFileStorage;
        $pearHttpSessionLoaderFactory = $this->defaultsProvider->pearHttpSessionLoaderFactory();
        $dataLoaderFactory = $this->defaultsProvider->dataLoaderFactory();
        $errorLogWriterFactory = $this->defaultsProvider->errorLogWriterFactory();
        $compiledTemplateLoaderStateFactory = $this->defaultsProvider->compiledTemplateLoaderStateFactory();
        $compiledTemplateLoaderFactory = $this->defaultsProvider->compiledTemplateLoaderFactory();
        $restorePasswordLogStorageFactory = $this->defaultsProvider->restorePasswordLogStorageFactory();
        $blockFileStorageFactory = $this->defaultsProvider->blockFileStorageFactory();
        $metaFileStorageFactory = $this->defaultsProvider->metaFileStorageFactory();
        $tabAliasFileStorageFactory = $this->defaultsProvider->tabAliasFileStorageFactory();
        $soapWsdlFileStorageFactory = $this->defaultsProvider->soapWsdlFileStorageFactory();
        $errorFileStorageFactory = $this->defaultsProvider->errorFileStorageFactory();
        $errorDemonstratorFileStorageFactory = $this->defaultsProvider->errorDemonstratorFileStorageFactory();
        $errorDemonstratorLoaderFactory = $this->defaultsProvider->errorDemonstratorLoaderFactory();
        $logFileStorageFactory = $this->defaultsProvider->logFileStorageFactory();
        $obfuscatorFileStorageFactory = $this->defaultsProvider->obfuscatorFileStorageFactory();
        $entityFileDiscoveryFactory = $this->defaultsProvider->entityFileDiscoveryFactory();
        $entityDescriptionFileStorageFactory = $this->defaultsProvider->entityDescriptionFileStorageFactory();
        $projectToolFileStorageFactory = $this->defaultsProvider->projectToolFileStorageFactory();
        $emailTemplateFileStorageFactory = $this->defaultsProvider->emailTemplateFileStorageFactory();
        $rootHtmlFileStorageFactory = $this->defaultsProvider->rootHtmlFileStorageFactory();
        $translationFileStorageFactory = $this->defaultsProvider->translationFileStorageFactory();
        $headerWriterFactory = $this->defaultsProvider->headerWriterFactory();
        $cookieWriterFactory = $this->defaultsProvider->cookieWriterFactory();
        $curlAdapterFactory = $this->defaultsProvider->curlAdapterFactory();
        $fileDataStorageFactory = $this->defaultsProvider->fileDataStorageFactory();
        $imageMetadataReaderFactory = $this->defaultsProvider->imageMetadataReaderFactory();
        $imageResourceFactoryFactory = $this->defaultsProvider->imageResourceFactoryFactory();
        $imageCanvasOperationsFactory = $this->defaultsProvider->imageCanvasOperationsFactory();
        $imageOutputWriterFactory = $this->defaultsProvider->imageOutputWriterFactory();
        $imageSourceFileStorageFactory = $this->defaultsProvider->imageSourceFileStorageFactory();
        $plainFileStorageFactory = $this->defaultsProvider->plainFileStorageFactory();
        $matcherRouteFileStorageFactory = $this->defaultsProvider->matcherRouteFileStorageFactory();
        $fileSystemStorageFactory = $this->defaultsProvider->fileSystemStorageFactory();

        return $container
            ->factory('cache_file_storage', static fn(container_interface $container): object => $cacheFileStorage)
            ->factory('cache_source_file_metadata', static fn(container_interface $container): object => $cacheSourceFileMetadata)
            ->factory('config_source_file_storage', static fn(container_interface $container): object => $configSourceFileStorage)
            ->factory('pear_http_session_loader', $pearHttpSessionLoaderFactory)
            ->factory('php_array_file_loader', static fn(container_interface $container): callable => $phpArrayFileLoader)
            ->factory('data_loader', $dataLoaderFactory, false)
            ->factory('error_log_writer', $errorLogWriterFactory)
            ->factory('restore_password_log_storage', $restorePasswordLogStorageFactory)
            ->factory('block_file_storage', $blockFileStorageFactory)
            ->factory('meta_file_storage', $metaFileStorageFactory)
            ->factory('tab_alias_file_storage', $tabAliasFileStorageFactory)
            ->factory('soap_wsdl_file_storage', $soapWsdlFileStorageFactory)
            ->factory('error_file_storage', $errorFileStorageFactory)
            ->factory('error_demonstrator_file_storage', $errorDemonstratorFileStorageFactory)
            ->factory('error_demonstrator_loader', $errorDemonstratorLoaderFactory)
            ->factory('log_file_storage', $logFileStorageFactory)
            ->factory('obfuscator_file_storage', $obfuscatorFileStorageFactory)
            ->factory('entity_file_discovery', $entityFileDiscoveryFactory)
            ->factory('entity_description_file_storage', $entityDescriptionFileStorageFactory)
            ->factory('project_tool_file_storage', $projectToolFileStorageFactory)
            ->factory('email_template_file_storage', $emailTemplateFileStorageFactory)
            ->factory('root_html_file_storage', $rootHtmlFileStorageFactory)
            ->factory('translation_file_storage', $translationFileStorageFactory)
            ->factory('header_writer', $headerWriterFactory)
            ->factory('cookie_writer', $cookieWriterFactory)
            ->factory('curl_adapter', $curlAdapterFactory)
            ->factory('native_session', static fn(container_interface $container): object => $nativeSession)
            ->factory('pear_http_session', static fn(container_interface $container): object => $pearHttpSession)
            ->factory('compiled_template_loader_state', $compiledTemplateLoaderStateFactory)
            ->factory('compiled_template_loader', $compiledTemplateLoaderFactory)
            ->factory('template_file_storage', static fn(container_interface $container): object => $templateFileStorage)
            ->factory('warning_capture', static fn(container_interface $container): object => $warningCapture)
            ->factory('file_data_storage', $fileDataStorageFactory)
            ->factory('image_metadata_reader', $imageMetadataReaderFactory)
            ->factory('image_resource_factory', $imageResourceFactoryFactory)
            ->factory('image_canvas_operations', $imageCanvasOperationsFactory)
            ->factory('image_output_writer', $imageOutputWriterFactory)
            ->factory('image_source_file_storage', $imageSourceFileStorageFactory)
            ->factory('plain_file_storage', $plainFileStorageFactory)
            ->factory('matcher_route_file_storage', $matcherRouteFileStorageFactory)
            ->factory('file_system_storage', $fileSystemStorageFactory);
    }
}
