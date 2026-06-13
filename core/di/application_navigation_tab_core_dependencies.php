<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_core_dependencies
{
    private application_navigation_tab_context_dependencies $context;
    private application_navigation_tab_service_factory_dependencies $serviceFactoryDependencies;
    private application_navigation_tab_model_factory_dependencies $modelFactory;

    public function __construct(container_interface $container)
    {
        $this->context = new application_navigation_tab_context_dependencies($container);
        $this->serviceFactoryDependencies = new application_navigation_tab_service_factory_dependencies($container);
        $this->modelFactory = new application_navigation_tab_model_factory_dependencies($container);
    }

    public function matcher(): mixed
    {
        return $this->context->matcher();
    }

    public function request(): mixed
    {
        return $this->context->request();
    }

    public function locale(): mixed
    {
        return $this->context->locale();
    }

    public function sessionFactory(): callable
    {
        return $this->context->sessionFactory();
    }

    public function requestInput(): mixed
    {
        return $this->context->requestInput();
    }

    public function roleFactory(): callable
    {
        return $this->serviceFactoryDependencies->roleFactory();
    }

    public function transferFactory(): callable
    {
        return $this->serviceFactoryDependencies->transferFactory();
    }

    public function configFactory(): callable
    {
        return $this->serviceFactoryDependencies->configFactory();
    }

    public function headerFactory(): callable
    {
        return $this->serviceFactoryDependencies->headerFactory();
    }

    public function applicationFactory(): callable
    {
        return $this->serviceFactoryDependencies->applicationFactory();
    }

    public function debugFactory(): callable
    {
        return $this->serviceFactoryDependencies->debugFactory();
    }

    public function jsonFactory(): callable
    {
        return $this->serviceFactoryDependencies->jsonFactory();
    }

    public function dataLoaderFactory(): callable
    {
        return $this->serviceFactoryDependencies->dataLoaderFactory();
    }

    public function errorFactory(): callable
    {
        return $this->serviceFactoryDependencies->errorFactory();
    }

    public function cookieFactory(): callable
    {
        return $this->serviceFactoryDependencies->cookieFactory();
    }

    public function reflectorFactory(): callable
    {
        return $this->serviceFactoryDependencies->reflectorFactory();
    }

    public function entityFactory(): callable
    {
        return $this->modelFactory->entityFactory();
    }

    public function pagerFactory(): callable
    {
        return $this->modelFactory->pagerFactory();
    }

    public function obfuscatorFactory(): callable
    {
        return $this->modelFactory->obfuscatorFactory();
    }

    public function imageModifyFactory(): callable
    {
        return $this->modelFactory->imageModifyFactory();
    }

    public function userFactory(): callable
    {
        return $this->modelFactory->userFactory();
    }

    public function dateFactory(): callable
    {
        return $this->modelFactory->dateFactory();
    }
}
