<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_core_request_array_transform_helper_dependencies
{
    private application_core_request_array_adducer_transform_helper_dependencies $arrayAdducer;
    private application_core_request_recursive_merger_transform_helper_dependencies $recursiveMerger;

    public function __construct(container_interface $container)
    {
        $this->arrayAdducer = new application_core_request_array_adducer_transform_helper_dependencies($container);
        $this->recursiveMerger = new application_core_request_recursive_merger_transform_helper_dependencies($container);
    }

    public function arrayAdducer(): callable
    {
        return $this->arrayAdducer->arrayAdducer();
    }

    public function recursiveMerger(): callable
    {
        return $this->recursiveMerger->recursiveMerger();
    }
}
