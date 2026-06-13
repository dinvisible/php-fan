<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_infrastructure_config_cache_request_header_dependencies
{
    private application_infrastructure_config_cache_request_input_dependencies $requestInput;
    private application_infrastructure_config_cache_header_writer_dependencies $headerWriter;

    public function __construct(container_interface $container)
    {
        $this->requestInput = new application_infrastructure_config_cache_request_input_dependencies($container);
        $this->headerWriter = new application_infrastructure_config_cache_header_writer_dependencies($container);
    }

    public function requestInput(): mixed
    {
        return $this->requestInput->requestInput();
    }

    public function headerWriter(): mixed
    {
        return $this->headerWriter->headerWriter();
    }
}
