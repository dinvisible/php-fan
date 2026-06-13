<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_route_locale_context_group
{
    private base_dependency_locale_factory_context_group $locale;
    private base_dependency_matcher_factory_context_group $matcher;

    public function __construct(container_interface $container)
    {
        $this->locale = new base_dependency_locale_factory_context_group($container);
        $this->matcher = new base_dependency_matcher_factory_context_group($container);
    }

    public function dependencies(): array
    {
        return array_merge(
            $this->locale->dependencies(),
            $this->matcher->dependencies()
        );
    }
}
