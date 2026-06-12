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
            ->factory(service_id::CACHE_FILE_STORAGE, static fn(container_interface $container): object => $cacheFileStorage)
            ->factory(service_id::CACHE_SOURCE_FILE_METADATA, static fn(container_interface $container): object => $cacheSourceFileMetadata)
            ->factory(service_id::CONFIG_SOURCE_FILE_STORAGE, static fn(container_interface $container): object => $configSourceFileStorage)
            ->factory(service_id::PEAR_HTTP_SESSION_LOADER, $pearHttpSessionLoaderFactory)
            ->factory(service_id::PHP_ARRAY_FILE_LOADER, static fn(container_interface $container): callable => $phpArrayFileLoader)
            ->factory(service_id::DATA_LOADER, $dataLoaderFactory, false)
            ->factory(service_id::ERROR_LOG_WRITER, $errorLogWriterFactory)
            ->factory(service_id::RESTORE_PASSWORD_LOG_STORAGE, $restorePasswordLogStorageFactory)
            ->factory(service_id::BLOCK_FILE_STORAGE, $blockFileStorageFactory)
            ->factory(service_id::META_FILE_STORAGE, $metaFileStorageFactory)
            ->factory(service_id::TAB_ALIAS_FILE_STORAGE, $tabAliasFileStorageFactory)
            ->factory(service_id::SOAP_WSDL_FILE_STORAGE, $soapWsdlFileStorageFactory)
            ->factory(service_id::ERROR_FILE_STORAGE, $errorFileStorageFactory)
            ->factory(service_id::ERROR_DEMONSTRATOR_FILE_STORAGE, $errorDemonstratorFileStorageFactory)
            ->factory(service_id::ERROR_DEMONSTRATOR_LOADER, $errorDemonstratorLoaderFactory)
            ->factory(service_id::LOG_FILE_STORAGE, $logFileStorageFactory)
            ->factory(service_id::OBFUSCATOR_FILE_STORAGE, $obfuscatorFileStorageFactory)
            ->factory(service_id::ENTITY_FILE_DISCOVERY, $entityFileDiscoveryFactory)
            ->factory(service_id::ENTITY_DESCRIPTION_FILE_STORAGE, $entityDescriptionFileStorageFactory)
            ->factory(service_id::PROJECT_TOOL_FILE_STORAGE, $projectToolFileStorageFactory)
            ->factory(service_id::EMAIL_TEMPLATE_FILE_STORAGE, $emailTemplateFileStorageFactory)
            ->factory(service_id::ROOT_HTML_FILE_STORAGE, $rootHtmlFileStorageFactory)
            ->factory(service_id::TRANSLATION_FILE_STORAGE, $translationFileStorageFactory)
            ->factory(service_id::HEADER_WRITER, $headerWriterFactory)
            ->factory(service_id::COOKIE_WRITER, $cookieWriterFactory)
            ->factory(service_id::CURL_ADAPTER, $curlAdapterFactory)
            ->factory(service_id::NATIVE_SESSION, static fn(container_interface $container): object => $nativeSession)
            ->factory(service_id::PEAR_HTTP_SESSION, static fn(container_interface $container): object => $pearHttpSession)
            ->factory(service_id::COMPILED_TEMPLATE_LOADER_STATE, $compiledTemplateLoaderStateFactory)
            ->factory(service_id::COMPILED_TEMPLATE_LOADER, $compiledTemplateLoaderFactory)
            ->factory(service_id::TEMPLATE_FILE_STORAGE, static fn(container_interface $container): object => $templateFileStorage)
            ->factory(service_id::WARNING_CAPTURE, static fn(container_interface $container): object => $warningCapture)
            ->factory(service_id::FILE_DATA_STORAGE, $fileDataStorageFactory)
            ->factory(service_id::IMAGE_METADATA_READER, $imageMetadataReaderFactory)
            ->factory(service_id::IMAGE_RESOURCE_FACTORY, $imageResourceFactoryFactory)
            ->factory(service_id::IMAGE_CANVAS_OPERATIONS, $imageCanvasOperationsFactory)
            ->factory(service_id::IMAGE_OUTPUT_WRITER, $imageOutputWriterFactory)
            ->factory(service_id::IMAGE_SOURCE_FILE_STORAGE, $imageSourceFileStorageFactory)
            ->factory(service_id::PLAIN_FILE_STORAGE, $plainFileStorageFactory)
            ->factory(service_id::MATCHER_ROUTE_FILE_STORAGE, $matcherRouteFileStorageFactory)
            ->factory(service_id::FILE_SYSTEM_STORAGE, $fileSystemStorageFactory);
    }
}
