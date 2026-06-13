<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_support_array_transform_dependencies
{
    private application_navigation_tab_array_adducer_transform_dependencies $arrayAdducer;
    private application_navigation_tab_recursive_merger_transform_dependencies $recursiveMerger;

    public function __construct(container_interface $container)
    {
        $this->arrayAdducer = new application_navigation_tab_array_adducer_transform_dependencies($container);
        $this->recursiveMerger = new application_navigation_tab_recursive_merger_transform_dependencies($container);
    }

    public function arrayAdducer(): mixed
    {
        return $this->arrayAdducer->arrayAdducer();
    }

    public function recursiveMerger(): mixed
    {
        return $this->recursiveMerger->recursiveMerger();
    }
}
