<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_core_service_registrar
{
    public function register(container $container, application_service_graph_registration_context $context): container
    {
        $coreServiceCreator = $context->coreServiceCreator;

        return $container
            ->factory(
                'request',
                static fn(container_interface $container): mixed => $coreServiceCreator->createRequestService($container, $context->requestServiceFactory)
            )
            ->factory(
                'role',
                static fn(container_interface $container): mixed => $coreServiceCreator->createRoleService($container, $context->roleServiceFactory)
            )
            ->factory(
                'error',
                static fn(container_interface $container): mixed => $coreServiceCreator->createErrorService($container, $context->errorServiceFactory)
            )
            ->factory(
                'header',
                static fn(container_interface $container): mixed => $coreServiceCreator->createHeaderService($container, $context->headerServiceFactory)
            )
            ->factory(
                'locale',
                static fn(container_interface $container): mixed => $coreServiceCreator->createLocaleService(
                    $container,
                    $context->localeServiceFactory
                )
            )
            ->factory(
                'application',
                static fn(container_interface $container): mixed => $coreServiceCreator->createApplicationService(
                    $container,
                    $context->applicationServiceFactory
                )
            )
            ->factory(
                'matcher',
                static fn(container_interface $container): mixed => $coreServiceCreator->createMatcherService(
                    $container,
                    $context->matcherItemFactory,
                    $context->matcherItemComponentFactory,
                    $context->matcherServiceFactory
                )
            )
            ->factory(
                'reflector',
                static fn(container_interface $container): mixed => $coreServiceCreator->createReflectorService(
                    $container,
                    $context->reflectorServiceFactory
                )
            )
            ->factory(
                'debug',
                static fn(container_interface $container): mixed => $coreServiceCreator->createDebugService($container, $context->debugServiceFactory)
            )
            ->factory(
                'timer',
                static fn(container_interface $container): mixed => $coreServiceCreator->createTimerService(
                    $container,
                    $context->timerProgramFactory,
                    $context->timerServiceFactory
                )
            );
    }
}
