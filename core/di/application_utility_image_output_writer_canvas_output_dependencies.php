<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_utility_image_output_writer_canvas_output_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function imageOutputWriter(): object
    {
        return $this->container->get(service_id::IMAGE_OUTPUT_WRITER);
    }
}
