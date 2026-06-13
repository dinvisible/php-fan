<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_resolver
{
    public function resolve(?object $tab, ?container_interface $container): array
    {
        $defaults = (new base_dependency_defaults())->dependencies();

        if ($tab !== null && method_exists($tab, 'getBlockDependencies')) {
            return array_merge($defaults, $tab->getBlockDependencies());
        }

        if ($container === null) {
            return $defaults;
        }

        return array_merge(
            $defaults,
            (new base_dependency_context_group($container))->dependencies(),
            (new base_dependency_factory_group($container))->dependencies(),
            (new base_dependency_helper_group($container))->dependencies(),
            (new base_dependency_storage_group($container))->dependencies(),
            (new base_dependency_view_meta_group($container))->dependencies()
        );
    }
}
