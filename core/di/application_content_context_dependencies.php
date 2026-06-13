<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_content_context_dependencies
{
    private application_content_localization_context_dependencies $localization;
    private application_content_error_block_context_dependencies $errorBlock;
    private application_content_request_matcher_context_dependencies $requestMatcher;

    public function __construct(container_interface $container)
    {
        $this->localization = new application_content_localization_context_dependencies($container);
        $this->errorBlock = new application_content_error_block_context_dependencies($container);
        $this->requestMatcher = new application_content_request_matcher_context_dependencies($container);
    }

    public function locale(): object
    {
        return $this->localization->locale();
    }

    public function tabFactory(): callable
    {
        return $this->localization->tabFactory();
    }

    public function error(): object
    {
        return $this->errorBlock->error();
    }

    public function blockContext(): object
    {
        return $this->errorBlock->blockContext();
    }

    public function matcher(): object
    {
        return $this->requestMatcher->matcher();
    }

    public function requestInput(): object
    {
        return $this->requestMatcher->requestInput();
    }
}
