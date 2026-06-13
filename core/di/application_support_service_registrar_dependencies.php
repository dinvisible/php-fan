<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_support_service_registrar_dependencies
{
    private application_support_error_demonstrator_registrar_dependencies $errorDemonstrator;
    private application_support_exception_factory_registrar_dependencies $exceptionFactory;
    private application_support_meta_view_registrar_dependencies $metaView;
    private application_support_bootstrap_context_registrar_dependencies $bootstrapContext;

    public function __construct(container_interface $container)
    {
        $this->errorDemonstrator = new application_support_error_demonstrator_registrar_dependencies($container);
        $this->exceptionFactory = new application_support_exception_factory_registrar_dependencies($container);
        $this->metaView = new application_support_meta_view_registrar_dependencies($container);
        $this->bootstrapContext = new application_support_bootstrap_context_registrar_dependencies($container);
    }

    public function headerWriter(): object
    {
        return $this->exceptionFactory->headerWriter();
    }

    public function errorLogWriter(): object
    {
        return $this->errorDemonstrator->errorLogWriter();
    }

    public function errorDemonstratorFileStorage(): object
    {
        return $this->errorDemonstrator->errorDemonstratorFileStorage();
    }

    public function errorDemonstratorLoader(): object
    {
        return $this->errorDemonstrator->errorDemonstratorLoader();
    }

    public function bootstrapRuntime(): object
    {
        return $this->exceptionFactory->bootstrapRuntime();
    }

    public function request(): object
    {
        return $this->exceptionFactory->request();
    }

    public function error(): object
    {
        return $this->exceptionFactory->error();
    }

    public function delayedMetaFactory(): callable
    {
        return $this->metaView->delayedMetaFactory();
    }

    public function recursiveMerger(): callable
    {
        return $this->metaView->recursiveMerger();
    }

    public function arrayAdducer(): callable
    {
        return $this->metaView->arrayAdducer();
    }

    public function classNameResolver(): callable
    {
        return $this->metaView->classNameResolver();
    }

    public function viewLoaderJsonKeeperFactory(): callable
    {
        return $this->metaView->viewLoaderJsonKeeperFactory();
    }

    public function viewLoaderTextKeeperFactory(): callable
    {
        return $this->metaView->viewLoaderTextKeeperFactory();
    }

    public function viewKeeperFactory(): callable
    {
        return $this->metaView->viewKeeperFactory();
    }

    public function phpRuntimeSettings(): object
    {
        return $this->metaView->phpRuntimeSettings();
    }

    public function serviceListenerState(): object
    {
        return $this->bootstrapContext->serviceListenerState();
    }

    public function serviceSingleState(): object
    {
        return $this->bootstrapContext->serviceSingleState();
    }

    public function viewLoaderState(): object
    {
        return $this->bootstrapContext->viewLoaderState();
    }

    public function metaMakerState(): object
    {
        return $this->bootstrapContext->metaMakerState();
    }

    public function specFileImageRowState(): object
    {
        return $this->bootstrapContext->specFileImageRowState();
    }

    public function tabResolver(): callable
    {
        return $this->bootstrapContext->tabResolver();
    }

    public function bootstrapRuntimeResolver(): callable
    {
        return $this->bootstrapContext->bootstrapRuntimeResolver();
    }

    public function reflectionClassFactory(): object
    {
        return $this->bootstrapContext->reflectionClassFactory();
    }

    public function application(): object
    {
        return $this->bootstrapContext->application();
    }

    public function translation(): object
    {
        return $this->bootstrapContext->translation();
    }

    public function cacheFactory(): callable
    {
        return $this->bootstrapContext->cacheFactory();
    }

    public function entity(): object
    {
        return $this->bootstrapContext->entity();
    }

    public function imageModifyFactory(): callable
    {
        return $this->bootstrapContext->imageModifyFactory();
    }

    public function imageMetadataReader(): object
    {
        return $this->bootstrapContext->imageMetadataReader();
    }

    public function plainFileStorage(): object
    {
        return $this->bootstrapContext->plainFileStorage();
    }

    public function plainExceptionFactory(): callable
    {
        return $this->bootstrapContext->plainExceptionFactory();
    }

    public function transferExceptionFactory(): callable
    {
        return $this->bootstrapContext->transferExceptionFactory();
    }
}
