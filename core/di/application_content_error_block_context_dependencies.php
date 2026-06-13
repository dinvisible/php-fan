<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_content_error_block_context_dependencies
{
    private application_content_error_context_dependencies $error;
    private application_content_block_context_dependencies $blockContext;

    public function __construct(container_interface $container)
    {
        $this->error = new application_content_error_context_dependencies($container);
        $this->blockContext = new application_content_block_context_dependencies($container);
    }

    public function error(): object
    {
        return $this->error->error();
    }

    public function blockContext(): object
    {
        return $this->blockContext->blockContext();
    }
}
