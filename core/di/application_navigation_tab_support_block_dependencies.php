<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_support_block_dependencies
{
    private application_navigation_tab_support_block_factory_dependencies $blockFactory;
    private application_navigation_tab_support_block_exception_meta_dependencies $exceptionMeta;

    public function __construct(container_interface $container)
    {
        $this->blockFactory = new application_navigation_tab_support_block_factory_dependencies($container);
        $this->exceptionMeta = new application_navigation_tab_support_block_exception_meta_dependencies($container);
    }

    public function tabState(): mixed
    {
        return $this->blockFactory->tabState();
    }

    public function blockFactory(): mixed
    {
        return $this->blockFactory->blockFactory();
    }

    public function blockExceptionFactory(): mixed
    {
        return $this->exceptionMeta->blockExceptionFactory();
    }

    public function metaRowFactory(): mixed
    {
        return $this->exceptionMeta->metaRowFactory();
    }
}
