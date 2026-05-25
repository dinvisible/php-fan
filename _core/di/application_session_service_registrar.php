<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_session_service_registrar
{
    public function register(
        container $container,
        application_service_graph_registration_context $context
    ): container {
        $sessionServiceCreator = $context->sessionServiceCreator;
        $sessionServiceFactory = $context->sessionServiceFactory;
        $sessionEngineFactory = $context->sessionEngineFactory;

        return $container
            ->factory(
                'session',
                static fn(container_interface $container, mixed $nameSpace = null, mixed $group = 'custom'): mixed => $sessionServiceCreator->createSessionService(
                    $container,
                    $container->get('session_state'),
                    $sessionServiceFactory,
                    $sessionEngineFactory,
                    $nameSpace,
                    $group
                ),
                false
            );
    }
}
