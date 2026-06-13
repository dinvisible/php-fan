<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_utility_image_canvas_output_dependencies
{
    private application_utility_image_canvas_operations_canvas_output_dependencies $imageCanvasOperations;
    private application_utility_image_output_writer_canvas_output_dependencies $imageOutputWriter;

    public function __construct(container_interface $container)
    {
        $this->imageCanvasOperations = new application_utility_image_canvas_operations_canvas_output_dependencies($container);
        $this->imageOutputWriter = new application_utility_image_output_writer_canvas_output_dependencies($container);
    }

    public function imageCanvasOperations(): object
    {
        return $this->imageCanvasOperations->imageCanvasOperations();
    }

    public function imageOutputWriter(): object
    {
        return $this->imageOutputWriter->imageOutputWriter();
    }
}
