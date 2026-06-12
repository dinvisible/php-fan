<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_user_service_registrar
{
    public function register(
        container $container,
        application_service_graph_registration_context $context
    ): container {
        $userServiceCreator = $context->userServiceCreator;
        $userServiceFactory = $context->userServiceFactory;
        $userEngineFactory = $context->userEngineFactory;

        return $container
            ->factory(
                service_id::USER,
                static fn(container_interface $container, mixed $identifyer, ?string $reqSpace = null): mixed => $userServiceCreator->createUserService(
                    $container,
                    $container->get(service_id::USER_STATE),
                    $userServiceFactory,
                    $userEngineFactory,
                    $identifyer,
                    $reqSpace,
                    $container->get(service_id::BOOTSTRAP_RUNTIME),
                    $container->get(service_id::CONFIG),
                    static fn(string $type): mixed => $container->get(service_id::CACHE, $type)
                ),
                false
            )
            ->factory(service_id::CURRENT_USER, static fn(container_interface $container, ?string $reqSpace = null): mixed => $userServiceCreator->getCurrentUserService($container, $container->get(service_id::USER_STATE), $reqSpace), false)
            ->factory(service_id::CURRENT_USER_CHECKED, static fn(container_interface $container, ?string $reqSpace = null): mixed => $userServiceCreator->getCurrentUserServiceChecked($container, $container->get(service_id::USER_STATE), $reqSpace), false)
            ->factory(service_id::CURRENT_USER_SPACE, static fn(container_interface $container): string => $userServiceCreator->getCurrentUserSpace($container, $container->get(service_id::USER_STATE)));
    }
}
