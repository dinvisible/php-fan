<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_core_request_runtime_factory_dependencies
{
    private application_core_request_matcher_factory_runtime_dependencies $matcherFactory;
    private application_core_request_request_factory_runtime_dependencies $requestFactory;

    public function __construct(container_interface $container)
    {
        $this->matcherFactory = new application_core_request_matcher_factory_runtime_dependencies($container);
        $this->requestFactory = new application_core_request_request_factory_runtime_dependencies($container);
    }

    public function matcherFactory(): callable
    {
        return $this->matcherFactory->matcherFactory();
    }

    public function requestFactory(): callable
    {
        return $this->requestFactory->requestFactory();
    }
}
