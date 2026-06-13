<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_utility_helper_error_core_dependencies
{
    private application_utility_array_value_reader_helper_error_core_dependencies $arrayValueReader;
    private application_utility_error_factory_helper_error_core_dependencies $errorFactory;

    public function __construct(container_interface $container)
    {
        $this->arrayValueReader = new application_utility_array_value_reader_helper_error_core_dependencies($container);
        $this->errorFactory = new application_utility_error_factory_helper_error_core_dependencies($container);
    }

    public function arrayValueReader(): callable
    {
        return $this->arrayValueReader->arrayValueReader();
    }

    public function errorFactory(): callable
    {
        return $this->errorFactory->errorFactory();
    }
}
