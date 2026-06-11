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
                service_id::REQUEST,
                static fn(container_interface $container): mixed => $coreServiceCreator->createRequestService($container, $context->requestServiceFactory)
            )
            ->factory(
                service_id::ROLE,
                static fn(container_interface $container): mixed => $coreServiceCreator->createRoleService($container, $context->roleServiceFactory)
            )
            ->factory(
                service_id::ERROR,
                static fn(container_interface $container): mixed => $coreServiceCreator->createErrorService($container, $context->errorServiceFactory)
            )
            ->factory(
                service_id::HEADER,
                static fn(container_interface $container): mixed => $coreServiceCreator->createHeaderService($container, $context->headerServiceFactory)
            )
            ->factory(
                service_id::LOCALE,
                static fn(container_interface $container): mixed => $coreServiceCreator->createLocaleService(
                    $container,
                    $context->localeServiceFactory
                )
            )
            ->factory(
                service_id::APPLICATION,
                static fn(container_interface $container): mixed => $coreServiceCreator->createApplicationService(
                    $container,
                    $context->applicationServiceFactory
                )
            )
            ->factory(
                service_id::MATCHER,
                static fn(container_interface $container): mixed => $coreServiceCreator->createMatcherService(
                    $container,
                    $context->matcherItemFactory,
                    $context->matcherItemComponentFactory,
                    $context->matcherServiceFactory
                )
            )
            ->factory(
                service_id::REFLECTOR,
                static fn(container_interface $container): mixed => $coreServiceCreator->createReflectorService(
                    $container,
                    $context->reflectorServiceFactory
                )
            )
            ->factory(
                service_id::DEBUG,
                static fn(container_interface $container): mixed => $coreServiceCreator->createDebugService($container, $context->debugServiceFactory)
            )
            ->factory(
                service_id::TIMER,
                static fn(container_interface $container): mixed => $coreServiceCreator->createTimerService(
                    $container,
                    $context->timerProgramFactory,
                    $context->timerServiceFactory
                )
            );
    }
}
