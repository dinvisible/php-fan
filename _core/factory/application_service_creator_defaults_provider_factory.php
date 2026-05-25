<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_service_creator_defaults_provider_factory
{
    private \Closure $coreServiceCreatorFactory;
    private \Closure $contentServiceCreatorFactory;
    private \Closure $navigationServiceCreatorFactory;
    private \Closure $controllerServiceCreatorFactory;
    private \Closure $infrastructureServiceCreatorFactory;
    private \Closure $clientServiceCreatorFactory;
    private \Closure $pagerServiceCreatorFactory;
    private \Closure $utilityServiceCreatorFactory;
    private \Closure $sessionServiceCreatorFactory;
    private \Closure $userServiceCreatorFactory;

    public function __construct(
        ?callable $coreServiceCreatorFactory = null,
        ?callable $contentServiceCreatorFactory = null,
        ?callable $navigationServiceCreatorFactory = null,
        ?callable $controllerServiceCreatorFactory = null,
        ?callable $infrastructureServiceCreatorFactory = null,
        ?callable $clientServiceCreatorFactory = null,
        ?callable $pagerServiceCreatorFactory = null,
        ?callable $utilityServiceCreatorFactory = null,
        ?callable $sessionServiceCreatorFactory = null,
        ?callable $userServiceCreatorFactory = null
    )
    {
        $this->coreServiceCreatorFactory = \Closure::fromCallable(
            $coreServiceCreatorFactory
                ?? static fn(): application_core_service_creator => new application_core_service_creator()
        );
        $this->contentServiceCreatorFactory = \Closure::fromCallable(
            $contentServiceCreatorFactory
                ?? static fn(): application_content_service_creator => new application_content_service_creator()
        );
        $this->navigationServiceCreatorFactory = \Closure::fromCallable(
            $navigationServiceCreatorFactory
                ?? static fn(): application_navigation_service_creator => new application_navigation_service_creator()
        );
        $this->controllerServiceCreatorFactory = \Closure::fromCallable(
            $controllerServiceCreatorFactory
                ?? static fn(): application_controller_service_creator => new application_controller_service_creator()
        );
        $this->infrastructureServiceCreatorFactory = \Closure::fromCallable(
            $infrastructureServiceCreatorFactory
                ?? static fn(): application_infrastructure_service_creator => new application_infrastructure_service_creator(
                    static fn(object $serializerOperations, callable $serviceExceptionFactory, callable $shortClassNameResolver): callable => new config_row_factory(
                        $serializerOperations,
                        $serviceExceptionFactory,
                        $shortClassNameResolver
                    )
                )
        );
        $this->clientServiceCreatorFactory = \Closure::fromCallable(
            $clientServiceCreatorFactory
                ?? static fn(): application_client_service_creator => new application_client_service_creator()
        );
        $this->pagerServiceCreatorFactory = \Closure::fromCallable(
            $pagerServiceCreatorFactory
                ?? static fn(): application_pager_service_creator => new application_pager_service_creator()
        );
        $this->utilityServiceCreatorFactory = \Closure::fromCallable(
            $utilityServiceCreatorFactory
                ?? static fn(): application_utility_service_creator => new application_utility_service_creator(new date_exception_factory())
        );
        $this->sessionServiceCreatorFactory = \Closure::fromCallable(
            $sessionServiceCreatorFactory
                ?? static fn(): application_session_service_creator => new application_session_service_creator(new fatal_exception_factory())
        );
        $this->userServiceCreatorFactory = \Closure::fromCallable(
            $userServiceCreatorFactory
                ?? static fn(): application_user_service_creator => new application_user_service_creator()
        );
    }

    public function __invoke(): application_service_creator_defaults_provider
    {
        return new application_service_creator_defaults_provider(
            ($this->coreServiceCreatorFactory)(),
            ($this->contentServiceCreatorFactory)(),
            ($this->navigationServiceCreatorFactory)(),
            ($this->controllerServiceCreatorFactory)(),
            ($this->infrastructureServiceCreatorFactory)(),
            ($this->clientServiceCreatorFactory)(),
            ($this->pagerServiceCreatorFactory)(),
            ($this->utilityServiceCreatorFactory)(),
            ($this->sessionServiceCreatorFactory)(),
            ($this->userServiceCreatorFactory)()
        );
    }
}
