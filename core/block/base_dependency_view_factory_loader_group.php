<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_view_factory_loader_group
{
    private base_dependency_view_parser_exception_factory_loader_group $viewParserException;
    private base_dependency_view_router_factory_loader_group $viewRouter;

    public function __construct(container_interface $container)
    {
        $this->viewParserException = new base_dependency_view_parser_exception_factory_loader_group($container);
        $this->viewRouter = new base_dependency_view_router_factory_loader_group($container);
    }

    public function dependencies(): array
    {
        return array_merge(
            $this->viewParserException->dependencies(),
            $this->viewRouter->dependencies()
        );
    }
}
