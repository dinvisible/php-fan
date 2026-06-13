<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_support_block_exception_meta_dependencies
{
    private application_navigation_tab_block_exception_factory_exception_meta_dependencies $blockExceptionFactory;
    private application_navigation_tab_meta_row_factory_exception_meta_dependencies $metaRowFactory;

    public function __construct(container_interface $container)
    {
        $this->blockExceptionFactory = new application_navigation_tab_block_exception_factory_exception_meta_dependencies($container);
        $this->metaRowFactory = new application_navigation_tab_meta_row_factory_exception_meta_dependencies($container);
    }

    public function blockExceptionFactory(): mixed
    {
        return $this->blockExceptionFactory->blockExceptionFactory();
    }

    public function metaRowFactory(): mixed
    {
        return $this->metaRowFactory->metaRowFactory();
    }
}
