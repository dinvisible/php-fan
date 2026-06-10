<?php

declare(strict_types=1);

namespace fan\core\di;

final class bootstrap_runtime_state_factory_defaults_provider_factory
{
    public function __invoke(): callable
    {

        return (new bootstrap_runtime_state_defaults_provider_factory())()->runtimeStateFactory();
    }
}
