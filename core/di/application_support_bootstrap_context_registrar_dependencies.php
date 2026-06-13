<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_support_bootstrap_context_registrar_dependencies
{
    private application_support_service_listener_state_registrar_dependencies $serviceListenerState;
    private application_support_service_single_state_registrar_dependencies $serviceSingleState;
    private application_support_view_loader_state_registrar_dependencies $viewLoaderState;
    private application_support_meta_maker_state_registrar_dependencies $metaMakerState;
    private application_support_spec_file_image_row_state_registrar_dependencies $specFileImageRowState;
    private application_support_tab_resolver_registrar_dependencies $tabResolver;
    private application_support_bootstrap_runtime_registrar_dependencies $bootstrapRuntime;
    private application_support_reflection_class_factory_registrar_dependencies $reflectionClassFactory;
    private application_support_application_registrar_dependencies $application;
    private application_support_translation_registrar_dependencies $translation;
    private application_support_cache_factory_registrar_dependencies $cacheFactory;
    private application_support_entity_registrar_dependencies $entity;
    private application_support_image_modify_factory_registrar_dependencies $imageModifyFactory;
    private application_support_image_metadata_reader_registrar_dependencies $imageMetadataReader;
    private application_support_plain_file_storage_registrar_dependencies $plainFileStorage;
    private application_support_plain_exception_factory_registrar_dependencies $plainExceptionFactory;
    private application_support_transfer_exception_factory_registrar_dependencies $transferExceptionFactory;

    public function __construct(container_interface $container)
    {
        $this->serviceListenerState = new application_support_service_listener_state_registrar_dependencies($container);
        $this->serviceSingleState = new application_support_service_single_state_registrar_dependencies($container);
        $this->viewLoaderState = new application_support_view_loader_state_registrar_dependencies($container);
        $this->metaMakerState = new application_support_meta_maker_state_registrar_dependencies($container);
        $this->specFileImageRowState = new application_support_spec_file_image_row_state_registrar_dependencies($container);
        $this->tabResolver = new application_support_tab_resolver_registrar_dependencies($container);
        $this->bootstrapRuntime = new application_support_bootstrap_runtime_registrar_dependencies($container);
        $this->reflectionClassFactory = new application_support_reflection_class_factory_registrar_dependencies($container);
        $this->application = new application_support_application_registrar_dependencies($container);
        $this->translation = new application_support_translation_registrar_dependencies($container);
        $this->cacheFactory = new application_support_cache_factory_registrar_dependencies($container);
        $this->entity = new application_support_entity_registrar_dependencies($container);
        $this->imageModifyFactory = new application_support_image_modify_factory_registrar_dependencies($container);
        $this->imageMetadataReader = new application_support_image_metadata_reader_registrar_dependencies($container);
        $this->plainFileStorage = new application_support_plain_file_storage_registrar_dependencies($container);
        $this->plainExceptionFactory = new application_support_plain_exception_factory_registrar_dependencies($container);
        $this->transferExceptionFactory = new application_support_transfer_exception_factory_registrar_dependencies($container);
    }

    public function serviceListenerState(): object
    {
        return $this->serviceListenerState->serviceListenerState();
    }

    public function serviceSingleState(): object
    {
        return $this->serviceSingleState->serviceSingleState();
    }

    public function viewLoaderState(): object
    {
        return $this->viewLoaderState->viewLoaderState();
    }

    public function metaMakerState(): object
    {
        return $this->metaMakerState->metaMakerState();
    }

    public function specFileImageRowState(): object
    {
        return $this->specFileImageRowState->specFileImageRowState();
    }

    public function tabResolver(): callable
    {
        return $this->tabResolver->tabResolver();
    }

    public function bootstrapRuntimeResolver(): callable
    {
        return fn(): object => $this->bootstrapRuntime->bootstrapRuntime();
    }

    public function reflectionClassFactory(): object
    {
        return $this->reflectionClassFactory->reflectionClassFactory();
    }

    public function application(): object
    {
        return $this->application->application();
    }

    public function translation(): object
    {
        return $this->translation->translation();
    }

    public function cacheFactory(): callable
    {
        return $this->cacheFactory->cacheFactory();
    }

    public function entity(): object
    {
        return $this->entity->entity();
    }

    public function imageModifyFactory(): callable
    {
        return $this->imageModifyFactory->imageModifyFactory();
    }

    public function imageMetadataReader(): object
    {
        return $this->imageMetadataReader->imageMetadataReader();
    }

    public function plainFileStorage(): object
    {
        return $this->plainFileStorage->plainFileStorage();
    }

    public function plainExceptionFactory(): callable
    {
        return $this->plainExceptionFactory->plainExceptionFactory();
    }

    public function transferExceptionFactory(): callable
    {
        return $this->transferExceptionFactory->transferExceptionFactory();
    }
}
