<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_utility_image_canvas_operations_canvas_output_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function imageCanvasOperations(): object
    {
        return $this->container->get(service_id::IMAGE_CANVAS_OPERATIONS);
    }
}
