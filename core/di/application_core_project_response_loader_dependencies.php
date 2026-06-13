<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_core_project_response_loader_dependencies
{
    private application_core_project_header_writer_response_loader_dependencies $headerWriter;
    private application_core_project_php_array_file_loader_response_loader_dependencies $phpArrayFileLoader;

    public function __construct(container_interface $container)
    {
        $this->headerWriter = new application_core_project_header_writer_response_loader_dependencies($container);
        $this->phpArrayFileLoader = new application_core_project_php_array_file_loader_response_loader_dependencies($container);
    }

    public function headerWriter(): object
    {
        return $this->headerWriter->headerWriter();
    }

    public function phpArrayFileLoader(): object
    {
        return $this->phpArrayFileLoader->phpArrayFileLoader();
    }
}
