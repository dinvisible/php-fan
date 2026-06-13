<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_content_request_matcher_context_dependencies
{
    private application_content_matcher_context_dependencies $matcher;
    private application_content_request_input_context_dependencies $requestInput;

    public function __construct(container_interface $container)
    {
        $this->matcher = new application_content_matcher_context_dependencies($container);
        $this->requestInput = new application_content_request_input_context_dependencies($container);
    }

    public function matcher(): object
    {
        return $this->matcher->matcher();
    }

    public function requestInput(): object
    {
        return $this->requestInput->requestInput();
    }
}
