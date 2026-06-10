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
                'user',
                static fn(container_interface $container, mixed $identifyer, ?string $reqSpace = null): mixed => $userServiceCreator->createUserService(
                    $container,
                    $container->get('user_state'),
                    $userServiceFactory,
                    $userEngineFactory,
                    $identifyer,
                    $reqSpace,
                    $container->get('bootstrap_runtime'),
                    $container->get('config'),
                    static fn(string $type): mixed => $container->get('cache', $type)
                ),
                false
            )
            ->factory('current_user', static fn(container_interface $container, ?string $reqSpace = null): mixed => $userServiceCreator->getCurrentUserService($container, $container->get('user_state'), $reqSpace), false)
            ->factory('current_user_checked', static fn(container_interface $container, ?string $reqSpace = null): mixed => $userServiceCreator->getCurrentUserServiceChecked($container, $container->get('user_state'), $reqSpace), false)
            ->factory('current_user_space', static fn(container_interface $container): string => $userServiceCreator->getCurrentUserSpace($container, $container->get('user_state')));
    }
}
