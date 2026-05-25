<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_adapter_registry_defaults_provider
{
    public function __construct(
        private application_core_adapter_defaults_provider $coreAdapterDefaultsProvider,
        private application_storage_adapter_defaults_provider $storageAdapterDefaultsProvider,
        private application_runtime_adapter_defaults_provider $runtimeAdapterDefaultsProvider,
        private application_compiled_template_adapter_defaults_provider $compiledTemplateAdapterDefaultsProvider,
        private application_session_adapter_defaults_provider $sessionAdapterDefaultsProvider,
        private application_image_adapter_defaults_provider $imageAdapterDefaultsProvider
    ) {
    }

    public function warningCapture(): object
    {
        return $this->coreAdapterDefaultsProvider()->warningCapture();
    }

    public function cacheFileStorage(): object
    {
        return $this->storageAdapterDefaultsProvider()->cacheFileStorage();
    }

    public function cacheSourceFileMetadata(): object
    {
        return $this->storageAdapterDefaultsProvider()->cacheSourceFileMetadata();
    }

    public function configSourceFileStorage(): object
    {
        return $this->storageAdapterDefaultsProvider()->configSourceFileStorage();
    }

    public function nativeSession(): object
    {
        return $this->sessionAdapterDefaultsProvider()->nativeSession();
    }

    public function pearHttpSession(): object
    {
        return $this->sessionAdapterDefaultsProvider()->pearHttpSession();
    }

    public function templateFileStorage(): object
    {
        return $this->storageAdapterDefaultsProvider()->templateFileStorage();
    }

    public function modelRequestFileStorage(): object
    {
        return $this->storageAdapterDefaultsProvider()->modelRequestFileStorage();
    }

    public function phpArrayFileLoader(): callable
    {
        return $this->coreAdapterDefaultsProvider()->phpArrayFileLoader();
    }

    public function pearHttpSessionLoaderFactory(): callable
    {
        return $this->sessionAdapterDefaultsProvider()->pearHttpSessionLoaderFactory();
    }

    public function dataLoaderFactory(): callable
    {
        return $this->runtimeAdapterDefaultsProvider()->dataLoaderFactory();
    }

    public function errorLogWriterFactory(): callable
    {
        return $this->runtimeAdapterDefaultsProvider()->errorLogWriterFactory();
    }

    public function compiledTemplateLoaderStateFactory(): callable
    {
        return $this->compiledTemplateAdapterDefaultsProvider()->compiledTemplateLoaderStateFactory();
    }

    public function compiledTemplateLoaderFactory(): callable
    {
        return $this->compiledTemplateAdapterDefaultsProvider()->compiledTemplateLoaderFactory();
    }

    public function serializerOperationsFactory(): callable
    {
        return $this->coreAdapterDefaultsProvider()->serializerOperationsFactory();
    }

    public function restorePasswordLogStorageFactory(): callable
    {
        return $this->storageAdapterDefaultsProvider()->restorePasswordLogStorageFactory();
    }

    public function blockFileStorageFactory(): callable
    {
        return $this->storageAdapterDefaultsProvider()->blockFileStorageFactory();
    }

    public function metaFileStorageFactory(): callable
    {
        return $this->storageAdapterDefaultsProvider()->metaFileStorageFactory();
    }

    public function tabAliasFileStorageFactory(): callable
    {
        return $this->storageAdapterDefaultsProvider()->tabAliasFileStorageFactory();
    }

    public function soapWsdlFileStorageFactory(): callable
    {
        return $this->storageAdapterDefaultsProvider()->soapWsdlFileStorageFactory();
    }

    public function errorFileStorageFactory(): callable
    {
        return $this->storageAdapterDefaultsProvider()->errorFileStorageFactory();
    }

    public function errorDemonstratorFileStorageFactory(): callable
    {
        return $this->storageAdapterDefaultsProvider()->errorDemonstratorFileStorageFactory();
    }

    public function errorDemonstratorLoaderFactory(): callable
    {
        return $this->storageAdapterDefaultsProvider()->errorDemonstratorLoaderFactory();
    }

    public function logFileStorageFactory(): callable
    {
        return $this->storageAdapterDefaultsProvider()->logFileStorageFactory();
    }

    public function obfuscatorFileStorageFactory(): callable
    {
        return $this->storageAdapterDefaultsProvider()->obfuscatorFileStorageFactory();
    }

    public function entityFileDiscoveryFactory(): callable
    {
        return $this->storageAdapterDefaultsProvider()->entityFileDiscoveryFactory();
    }

    public function entityDescriptionFileStorageFactory(): callable
    {
        return $this->storageAdapterDefaultsProvider()->entityDescriptionFileStorageFactory();
    }

    public function projectToolFileStorageFactory(): callable
    {
        return $this->storageAdapterDefaultsProvider()->projectToolFileStorageFactory();
    }

    public function emailTemplateFileStorageFactory(): callable
    {
        return $this->storageAdapterDefaultsProvider()->emailTemplateFileStorageFactory();
    }

    public function rootHtmlFileStorageFactory(): callable
    {
        return $this->storageAdapterDefaultsProvider()->rootHtmlFileStorageFactory();
    }

    public function translationFileStorageFactory(): callable
    {
        return $this->storageAdapterDefaultsProvider()->translationFileStorageFactory();
    }

    public function headerWriterFactory(): callable
    {
        return $this->runtimeAdapterDefaultsProvider()->headerWriterFactory();
    }

    public function cookieWriterFactory(): callable
    {
        return $this->runtimeAdapterDefaultsProvider()->cookieWriterFactory();
    }

    public function curlAdapterFactory(): callable
    {
        return $this->runtimeAdapterDefaultsProvider()->curlAdapterFactory();
    }

    public function fileDataStorageFactory(): callable
    {
        return $this->storageAdapterDefaultsProvider()->fileDataStorageFactory();
    }

    public function imageMetadataReaderFactory(): callable
    {
        return $this->imageAdapterDefaultsProvider()->imageMetadataReaderFactory();
    }

    public function imageResourceFactoryFactory(): callable
    {
        return $this->imageAdapterDefaultsProvider()->imageResourceFactoryFactory();
    }

    public function imageCanvasOperationsFactory(): callable
    {
        return $this->imageAdapterDefaultsProvider()->imageCanvasOperationsFactory();
    }

    public function imageOutputWriterFactory(): callable
    {
        return $this->imageAdapterDefaultsProvider()->imageOutputWriterFactory();
    }

    public function imageSourceFileStorageFactory(): callable
    {
        return $this->storageAdapterDefaultsProvider()->imageSourceFileStorageFactory();
    }

    public function plainFileStorageFactory(): callable
    {
        return $this->storageAdapterDefaultsProvider()->plainFileStorageFactory();
    }

    public function matcherRouteFileStorageFactory(): callable
    {
        return $this->storageAdapterDefaultsProvider()->matcherRouteFileStorageFactory();
    }

    public function fileSystemStorageFactory(): callable
    {
        return $this->storageAdapterDefaultsProvider()->fileSystemStorageFactory();
    }

    private function storageAdapterDefaultsProvider(): application_storage_adapter_defaults_provider
    {
        return $this->storageAdapterDefaultsProvider;
    }

    private function coreAdapterDefaultsProvider(): application_core_adapter_defaults_provider
    {
        return $this->coreAdapterDefaultsProvider;
    }

    private function imageAdapterDefaultsProvider(): application_image_adapter_defaults_provider
    {
        return $this->imageAdapterDefaultsProvider;
    }

    private function sessionAdapterDefaultsProvider(): application_session_adapter_defaults_provider
    {
        return $this->sessionAdapterDefaultsProvider;
    }

    private function compiledTemplateAdapterDefaultsProvider(): application_compiled_template_adapter_defaults_provider
    {
        return $this->compiledTemplateAdapterDefaultsProvider;
    }

    private function runtimeAdapterDefaultsProvider(): application_runtime_adapter_defaults_provider
    {
        return $this->runtimeAdapterDefaultsProvider;
    }
}
