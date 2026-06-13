<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_support_class_helper_dependencies
{
    private application_navigation_tab_class_name_resolver_class_helper_dependencies $classNameResolver;
    private application_navigation_tab_short_class_name_resolver_class_helper_dependencies $shortClassNameResolver;

    public function __construct(container_interface $container)
    {
        $this->classNameResolver = new application_navigation_tab_class_name_resolver_class_helper_dependencies($container);
        $this->shortClassNameResolver = new application_navigation_tab_short_class_name_resolver_class_helper_dependencies($container);
    }

    public function classNameResolver(): mixed
    {
        return $this->classNameResolver->classNameResolver();
    }

    public function shortClassNameResolver(): mixed
    {
        return $this->shortClassNameResolver->shortClassNameResolver();
    }
}
