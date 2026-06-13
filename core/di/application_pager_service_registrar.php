<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\block\base;


final class application_pager_service_registrar
{
    public function __construct(private ?\Closure $dependenciesFactory = null)
    {
    }

    public function register(
        container $container,
        application_service_graph_registration_context $context
    ): container {
        $pagerServiceCreator = $context->pagerServiceCreator;
        $pagerServiceFactory = $context->pagerServiceFactory;
        $dependenciesFactory = $this->dependenciesFactory();

        return $container
            ->factory(
                service_id::PAGER,
                static fn(container_interface $container, string|base $block): mixed => $pagerServiceCreator->createPagerService(
                    $container,
                    $dependenciesFactory($container)->pagerState(),
                    $pagerServiceFactory,
                    $block
                ),
                false
            );
    }

    private function dependenciesFactory(): \Closure
    {
        return $this->dependenciesFactory
            ?? static fn(container_interface $container): application_pager_service_registrar_dependencies => new application_pager_service_registrar_dependencies($container);
    }
}
