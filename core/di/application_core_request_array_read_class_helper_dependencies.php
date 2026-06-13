<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_core_request_array_read_class_helper_dependencies
{
    private application_core_request_array_value_reader_helper_dependencies $arrayValueReader;
    private application_core_request_class_name_resolver_helper_dependencies $classNameResolver;

    public function __construct(container_interface $container)
    {
        $this->arrayValueReader = new application_core_request_array_value_reader_helper_dependencies($container);
        $this->classNameResolver = new application_core_request_class_name_resolver_helper_dependencies($container);
    }

    public function arrayValueReader(): callable
    {
        return $this->arrayValueReader->arrayValueReader();
    }

    public function classNameResolver(): callable
    {
        return $this->classNameResolver->classNameResolver();
    }
}
