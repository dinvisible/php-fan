<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_infrastructure_config_cache_exception_factory_dependencies
{
    private application_infrastructure_config_cache_core_fatal_exception_factory_dependencies $coreFatalExceptionFactory;
    private application_infrastructure_config_cache_error500_exception_factory_dependencies $error500ExceptionFactory;

    public function __construct(container_interface $container)
    {
        $this->coreFatalExceptionFactory = new application_infrastructure_config_cache_core_fatal_exception_factory_dependencies($container);
        $this->error500ExceptionFactory = new application_infrastructure_config_cache_error500_exception_factory_dependencies($container);
    }

    public function coreFatalExceptionFactory(): mixed
    {
        return $this->coreFatalExceptionFactory->coreFatalExceptionFactory();
    }

    public function error500ExceptionFactory(): mixed
    {
        return $this->error500ExceptionFactory->error500ExceptionFactory();
    }
}
