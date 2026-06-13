<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_support_block_factory_dependencies
{
    private application_navigation_tab_tab_state_block_factory_dependencies $tabState;
    private application_navigation_tab_block_factory_instance_block_factory_dependencies $blockFactory;

    public function __construct(container_interface $container)
    {
        $this->tabState = new application_navigation_tab_tab_state_block_factory_dependencies($container);
        $this->blockFactory = new application_navigation_tab_block_factory_instance_block_factory_dependencies($container);
    }

    public function tabState(): mixed
    {
        return $this->tabState->tabState();
    }

    public function blockFactory(): mixed
    {
        return $this->blockFactory->blockFactory();
    }
}
