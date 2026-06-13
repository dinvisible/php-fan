<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_support_asset_dependencies
{
    private application_navigation_tab_alias_asset_dependencies $alias;
    private application_navigation_tab_media_error_asset_dependencies $mediaError;

    public function __construct(container_interface $container)
    {
        $this->alias = new application_navigation_tab_alias_asset_dependencies($container);
        $this->mediaError = new application_navigation_tab_media_error_asset_dependencies($container);
    }

    public function tabAliasFileStorage(): mixed
    {
        return $this->alias->tabAliasFileStorage();
    }

    public function imageMetadataReader(): mixed
    {
        return $this->mediaError->imageMetadataReader();
    }

    public function errorLogWriter(): mixed
    {
        return $this->mediaError->errorLogWriter();
    }
}
