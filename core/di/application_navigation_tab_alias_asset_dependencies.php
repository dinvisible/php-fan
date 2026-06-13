<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_alias_asset_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function tabAliasFileStorage(): mixed
    {
        return $this->container->get(service_id::TAB_ALIAS_FILE_STORAGE);
    }
}
