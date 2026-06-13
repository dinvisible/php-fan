<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_pager_context_dependencies
{
    private application_pager_entity_context_dependencies $entity;
    private application_pager_tab_context_dependencies $tabContext;
    private application_pager_request_context_dependencies $request;

    public function __construct(container_interface $container)
    {
        $this->entity = new application_pager_entity_context_dependencies($container);
        $this->tabContext = new application_pager_tab_context_dependencies($container);
        $this->request = new application_pager_request_context_dependencies($container);
    }

    public function entityFactory(): callable
    {
        return $this->entity->entityFactory();
    }

    public function tab(): object
    {
        return $this->tabContext->tab();
    }

    public function tabFactory(): callable
    {
        return $this->tabContext->tabFactory();
    }

    public function requestFactory(): callable
    {
        return $this->request->requestFactory();
    }
}
