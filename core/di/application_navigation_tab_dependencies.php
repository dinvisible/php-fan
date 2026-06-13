<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_dependencies
{
    private application_navigation_tab_core_dependencies $core;
    private application_navigation_tab_storage_dependencies $storage;
    private application_navigation_tab_support_dependencies $support;

    public function __construct(container_interface $container)
    {
        $this->core = new application_navigation_tab_core_dependencies($container);
        $this->storage = new application_navigation_tab_storage_dependencies($container);
        $this->support = new application_navigation_tab_support_dependencies($container);
    }

    public function matcher(): mixed
    {
        return $this->core->matcher();
    }

    public function request(): mixed
    {
        return $this->core->request();
    }

    public function locale(): mixed
    {
        return $this->core->locale();
    }

    public function sessionFactory(): callable
    {
        return $this->core->sessionFactory();
    }

    public function requestInput(): mixed
    {
        return $this->core->requestInput();
    }

    public function roleFactory(): callable
    {
        return $this->core->roleFactory();
    }

    public function transferFactory(): callable
    {
        return $this->core->transferFactory();
    }

    public function configFactory(): callable
    {
        return $this->core->configFactory();
    }

    public function headerFactory(): callable
    {
        return $this->core->headerFactory();
    }

    public function applicationFactory(): callable
    {
        return $this->core->applicationFactory();
    }

    public function debugFactory(): callable
    {
        return $this->core->debugFactory();
    }

    public function jsonFactory(): callable
    {
        return $this->core->jsonFactory();
    }

    public function dataLoaderFactory(): callable
    {
        return $this->core->dataLoaderFactory();
    }

    public function errorFactory(): callable
    {
        return $this->core->errorFactory();
    }

    public function cookieFactory(): callable
    {
        return $this->core->cookieFactory();
    }

    public function reflectorFactory(): callable
    {
        return $this->core->reflectorFactory();
    }

    public function entityFactory(): callable
    {
        return $this->core->entityFactory();
    }

    public function pagerFactory(): callable
    {
        return $this->core->pagerFactory();
    }

    public function obfuscatorFactory(): callable
    {
        return $this->core->obfuscatorFactory();
    }

    public function imageModifyFactory(): callable
    {
        return $this->core->imageModifyFactory();
    }

    public function userFactory(): callable
    {
        return $this->core->userFactory();
    }

    public function dateFactory(): callable
    {
        return $this->core->dateFactory();
    }

    public function tabState(): mixed
    {
        return $this->support->tabState();
    }

    public function phpArrayFileLoader(): mixed
    {
        return $this->support->phpArrayFileLoader();
    }

    public function blockFactory(): mixed
    {
        return $this->support->blockFactory();
    }

    public function blockExceptionFactory(): mixed
    {
        return $this->support->blockExceptionFactory();
    }

    public function metaRowFactory(): mixed
    {
        return $this->support->metaRowFactory();
    }

    public function tabAliasFileStorage(): mixed
    {
        return $this->support->tabAliasFileStorage();
    }

    public function arrayAdducer(): mixed
    {
        return $this->support->arrayAdducer();
    }

    public function recursiveMerger(): mixed
    {
        return $this->support->recursiveMerger();
    }

    public function arrayValueReader(): mixed
    {
        return $this->support->arrayValueReader();
    }

    public function classNameResolver(): mixed
    {
        return $this->support->classNameResolver();
    }

    public function arrayLikeChecker(): mixed
    {
        return $this->support->arrayLikeChecker();
    }

    public function shortClassNameResolver(): mixed
    {
        return $this->support->shortClassNameResolver();
    }

    public function imageMetadataReader(): mixed
    {
        return $this->support->imageMetadataReader();
    }

    public function errorLogWriter(): mixed
    {
        return $this->support->errorLogWriter();
    }

    public function blockFileStorage(): mixed
    {
        return $this->storage->blockFileStorage();
    }

    public function metaFileStorage(): mixed
    {
        return $this->storage->metaFileStorage();
    }

    public function projectToolFileStorage(): mixed
    {
        return $this->storage->projectToolFileStorage();
    }

    public function rootHtmlFileStorage(): mixed
    {
        return $this->storage->rootHtmlFileStorage();
    }

    public function uploadSizeLimitProvider(): mixed
    {
        return $this->storage->uploadSizeLimitProvider();
    }
}
