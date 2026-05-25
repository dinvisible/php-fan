<?php

declare(strict_types=1);

namespace fan\core\di;

final class bootstrap_runtime_service_factory_defaults_provider_factory
{
    public function __invoke(): callable
    {

        return (new bootstrap_runtime_service_defaults_provider_factory())()->runtimeServiceFactory();
    }
}
