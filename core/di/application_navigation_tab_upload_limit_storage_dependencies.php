<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_upload_limit_storage_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function uploadSizeLimitProvider(): mixed
    {
        return $this->container->get(service_id::UPLOAD_SIZE_LIMIT_PROVIDER);
    }
}
