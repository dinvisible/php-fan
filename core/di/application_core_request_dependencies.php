<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_core_request_dependencies
{
    private application_core_request_factory_dependencies $requestFactory;
    private application_core_request_helper_dependencies $helper;

    public function __construct(container_interface $container)
    {
        $this->requestFactory = new application_core_request_factory_dependencies($container);
        $this->helper = new application_core_request_helper_dependencies($container);
    }

    public function requestInput(): object
    {
        return $this->requestFactory->requestInput();
    }

    public function jsonFactory(): callable
    {
        return $this->requestFactory->jsonFactory();
    }

    public function cookieFactory(): callable
    {
        return $this->requestFactory->cookieFactory();
    }

    public function matcherFactory(): callable
    {
        return $this->requestFactory->matcherFactory();
    }

    public function requestFactory(): callable
    {
        return $this->requestFactory->requestFactory();
    }

    public function arrayAdducer(): callable
    {
        return $this->helper->arrayAdducer();
    }

    public function recursiveMerger(): callable
    {
        return $this->helper->recursiveMerger();
    }

    public function arrayValueReader(): callable
    {
        return $this->helper->arrayValueReader();
    }

    public function classNameResolver(): callable
    {
        return $this->helper->classNameResolver();
    }
}
