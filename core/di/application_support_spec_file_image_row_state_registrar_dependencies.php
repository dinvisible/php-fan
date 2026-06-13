<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_support_spec_file_image_row_state_registrar_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function specFileImageRowState(): object
    {
        return $this->container->get(service_id::SPEC_FILE_IMAGE_ROW_STATE);
    }
}
