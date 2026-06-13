<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_support_helper_dependencies
{
    private application_navigation_tab_support_loader_dependencies $loader;
    private application_navigation_tab_support_array_helper_dependencies $array;
    private application_navigation_tab_support_class_helper_dependencies $class;

    public function __construct(container_interface $container)
    {
        $this->loader = new application_navigation_tab_support_loader_dependencies($container);
        $this->array = new application_navigation_tab_support_array_helper_dependencies($container);
        $this->class = new application_navigation_tab_support_class_helper_dependencies($container);
    }

    public function phpArrayFileLoader(): mixed
    {
        return $this->loader->phpArrayFileLoader();
    }

    public function arrayAdducer(): mixed
    {
        return $this->array->arrayAdducer();
    }

    public function recursiveMerger(): mixed
    {
        return $this->array->recursiveMerger();
    }

    public function arrayValueReader(): mixed
    {
        return $this->array->arrayValueReader();
    }

    public function classNameResolver(): mixed
    {
        return $this->class->classNameResolver();
    }

    public function arrayLikeChecker(): mixed
    {
        return $this->array->arrayLikeChecker();
    }

    public function shortClassNameResolver(): mixed
    {
        return $this->class->shortClassNameResolver();
    }
}
