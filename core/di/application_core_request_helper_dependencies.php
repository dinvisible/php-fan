<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_core_request_helper_dependencies
{
    private application_core_request_array_transform_helper_dependencies $arrayTransform;
    private application_core_request_array_read_class_helper_dependencies $arrayReadClass;

    public function __construct(container_interface $container)
    {
        $this->arrayTransform = new application_core_request_array_transform_helper_dependencies($container);
        $this->arrayReadClass = new application_core_request_array_read_class_helper_dependencies($container);
    }

    public function arrayAdducer(): callable
    {
        return $this->arrayTransform->arrayAdducer();
    }

    public function recursiveMerger(): callable
    {
        return $this->arrayTransform->recursiveMerger();
    }

    public function arrayValueReader(): callable
    {
        return $this->arrayReadClass->arrayValueReader();
    }

    public function classNameResolver(): callable
    {
        return $this->arrayReadClass->classNameResolver();
    }
}
