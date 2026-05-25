<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\block\base;


final class application_pager_service_registrar
{
    public function register(
        container $container,
        application_service_graph_registration_context $context
    ): container {
        $pagerServiceCreator = $context->pagerServiceCreator;
        $pagerServiceFactory = $context->pagerServiceFactory;

        return $container
            ->factory(
                'pager',
                static fn(container_interface $container, string|base $block): mixed => $pagerServiceCreator->createPagerService(
                    $container,
                    $container->get('pager_state'),
                    $pagerServiceFactory,
                    $block
                ),
                false
            );
    }
}
