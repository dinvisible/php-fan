<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_support_array_helper_dependencies
{
    private application_navigation_tab_support_array_transform_dependencies $transform;
    private application_navigation_tab_support_array_read_check_dependencies $readCheck;

    public function __construct(container_interface $container)
    {
        $this->transform = new application_navigation_tab_support_array_transform_dependencies($container);
        $this->readCheck = new application_navigation_tab_support_array_read_check_dependencies($container);
    }

    public function arrayAdducer(): mixed
    {
        return $this->transform->arrayAdducer();
    }

    public function recursiveMerger(): mixed
    {
        return $this->transform->recursiveMerger();
    }

    public function arrayValueReader(): mixed
    {
        return $this->readCheck->arrayValueReader();
    }

    public function arrayLikeChecker(): mixed
    {
        return $this->readCheck->arrayLikeChecker();
    }
}
