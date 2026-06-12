<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_service_registrar
{
    public function register(container $container, application_service_graph_registration_context $context): container
    {
        $navigationServiceCreator = $context->navigationServiceCreator;

        return $container
            ->factory(
                service_id::TAB,
                static fn(container_interface $container): mixed => $navigationServiceCreator->createTabService(
                    $container,
                    $context->tabDelegateFactory,
                    $context->tabViewParserFactory,
                    $context->tabServiceFactory
                )
            );
    }
}
