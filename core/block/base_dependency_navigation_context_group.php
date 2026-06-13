<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_navigation_context_group
{
    private base_dependency_reflector_context_group $reflector;
    private base_dependency_route_locale_context_group $routeLocale;

    public function __construct(container_interface $container)
    {
        $this->reflector = new base_dependency_reflector_context_group($container);
        $this->routeLocale = new base_dependency_route_locale_context_group($container);
    }

    public function dependencies(): array
    {
        return array_merge(
            $this->reflector->dependencies(),
            $this->routeLocale->dependencies()
        );
    }
}
