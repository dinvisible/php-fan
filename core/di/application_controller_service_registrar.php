<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_controller_service_registrar
{
    public function register(container $container, application_service_graph_registration_context $context): container
    {
        $controllerServiceCreator = $context->controllerServiceCreator;

        return $container
            ->factory(
                'plain',
                static fn(container_interface $container): mixed => $controllerServiceCreator->createPlainService(
                    $container,
                    $context->plainControllerFactory,
                    $context->plainServiceFactory
                )
            );
    }
}
