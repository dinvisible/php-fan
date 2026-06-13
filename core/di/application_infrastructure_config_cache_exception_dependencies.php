<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_infrastructure_config_cache_exception_dependencies
{
    private application_infrastructure_config_cache_exception_factory_dependencies $exceptionFactory;
    private application_infrastructure_config_cache_request_header_dependencies $requestHeader;

    public function __construct(container_interface $container)
    {
        $this->exceptionFactory = new application_infrastructure_config_cache_exception_factory_dependencies($container);
        $this->requestHeader = new application_infrastructure_config_cache_request_header_dependencies($container);
    }

    public function coreFatalExceptionFactory(): mixed
    {
        return $this->exceptionFactory->coreFatalExceptionFactory();
    }

    public function requestInput(): mixed
    {
        return $this->requestHeader->requestInput();
    }

    public function headerWriter(): mixed
    {
        return $this->requestHeader->headerWriter();
    }

    public function error500ExceptionFactory(): mixed
    {
        return $this->exceptionFactory->error500ExceptionFactory();
    }
}
