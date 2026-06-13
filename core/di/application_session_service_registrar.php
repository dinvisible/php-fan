<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_session_service_registrar
{
    public function __construct(private ?\Closure $dependenciesFactory = null)
    {
    }

    public function register(
        container $container,
        application_service_graph_registration_context $context
    ): container {
        $sessionServiceCreator = $context->sessionServiceCreator;
        $sessionServiceFactory = $context->sessionServiceFactory;
        $sessionEngineFactory = $context->sessionEngineFactory;
        $dependenciesFactory = $this->dependenciesFactory();

        return $container
            ->factory(
                service_id::SESSION,
                static fn(container_interface $container, mixed $nameSpace = null, mixed $group = 'custom'): mixed => $sessionServiceCreator->createSessionService(
                    $container,
                    $dependenciesFactory($container)->sessionState(),
                    $sessionServiceFactory,
                    $sessionEngineFactory,
                    $nameSpace,
                    $group
                ),
                false
            );
    }

    private function dependenciesFactory(): \Closure
    {
        return $this->dependenciesFactory
            ?? static fn(container_interface $container): application_session_service_registrar_dependencies => new application_session_service_registrar_dependencies($container);
    }
}
