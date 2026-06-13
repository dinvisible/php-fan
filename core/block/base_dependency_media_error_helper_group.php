<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_media_error_helper_group
{
    private base_dependency_image_metadata_media_error_helper_group $imageMetadata;
    private base_dependency_error_log_media_error_helper_group $errorLog;

    public function __construct(container_interface $container)
    {
        $this->imageMetadata = new base_dependency_image_metadata_media_error_helper_group($container);
        $this->errorLog = new base_dependency_error_log_media_error_helper_group($container);
    }

    public function dependencies(): array
    {
        return array_merge(
            $this->imageMetadata->dependencies(),
            $this->errorLog->dependencies()
        );
    }
}
