<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_media_error_asset_dependencies
{
    private application_navigation_tab_image_metadata_reader_asset_dependencies $imageMetadataReader;
    private application_navigation_tab_error_log_writer_asset_dependencies $errorLogWriter;

    public function __construct(container_interface $container)
    {
        $this->imageMetadataReader = new application_navigation_tab_image_metadata_reader_asset_dependencies($container);
        $this->errorLogWriter = new application_navigation_tab_error_log_writer_asset_dependencies($container);
    }

    public function imageMetadataReader(): mixed
    {
        return $this->imageMetadataReader->imageMetadataReader();
    }

    public function errorLogWriter(): mixed
    {
        return $this->errorLogWriter->errorLogWriter();
    }
}
