<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_user_service_registrar
{
    public function __construct(private ?\Closure $dependenciesFactory = null)
    {
    }

    public function register(
        container $container,
        application_service_graph_registration_context $context
    ): container {
        $userServiceCreator = $context->userServiceCreator;
        $userServiceFactory = $context->userServiceFactory;
        $userEngineFactory = $context->userEngineFactory;
        $dependenciesFactory = $this->dependenciesFactory();

        return $container
            ->factory(
                service_id::USER,
                static function (container_interface $container, mixed $identifyer, ?string $reqSpace = null) use ($dependenciesFactory, $userServiceCreator, $userServiceFactory, $userEngineFactory): mixed {
                    $dependencies = $dependenciesFactory($container);

                    return $userServiceCreator->createUserService(
                        $container,
                        $dependencies->userState(),
                        $userServiceFactory,
                        $userEngineFactory,
                        $identifyer,
                        $reqSpace,
                        $dependencies->bootstrapRuntime(),
                        $dependencies->config(),
                        $dependencies->cacheFactory()
                    );
                },
                false
            )
            ->factory(service_id::CURRENT_USER, static fn(container_interface $container, ?string $reqSpace = null): mixed => $userServiceCreator->getCurrentUserService($container, $dependenciesFactory($container)->userState(), $reqSpace), false)
            ->factory(service_id::CURRENT_USER_CHECKED, static fn(container_interface $container, ?string $reqSpace = null): mixed => $userServiceCreator->getCurrentUserServiceChecked($container, $dependenciesFactory($container)->userState(), $reqSpace), false)
            ->factory(service_id::CURRENT_USER_SPACE, static fn(container_interface $container): string => $userServiceCreator->getCurrentUserSpace($container, $dependenciesFactory($container)->userState()));
    }

    private function dependenciesFactory(): \Closure
    {
        return $this->dependenciesFactory
            ?? static fn(container_interface $container): application_user_service_registrar_dependencies => new application_user_service_registrar_dependencies($container);
    }
}
