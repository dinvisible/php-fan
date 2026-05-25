<?php

declare(strict_types=1);

namespace fan\core\di;
final class application_service_sub_factory_defaults_provider_factory
{
    private \Closure $matcherItemFactoryFactory;
    private \Closure $matcherItemComponentFactoryFactory;
    private \Closure $tabDelegateFactoryFactory;
    private \Closure $tabViewParserFactoryFactory;
    private \Closure $plainControllerFactoryFactory;
    private \Closure $blockFactoryFactory;
    private \Closure $blockExceptionFactoryFactory;

    public function __construct(
        ?callable $matcherItemFactoryFactory = null,
        ?callable $matcherItemComponentFactoryFactory = null,
        ?callable $tabDelegateFactoryFactory = null,
        ?callable $tabViewParserFactoryFactory = null,
        ?callable $plainControllerFactoryFactory = null,
        ?callable $blockFactoryFactory = null,
        ?callable $blockExceptionFactoryFactory = null
    )
    {
        $this->matcherItemFactoryFactory = \Closure::fromCallable(
            $matcherItemFactoryFactory
                ?? static fn(): callable => new matcher_item_factory()
        );
        $this->matcherItemComponentFactoryFactory = \Closure::fromCallable(
            $matcherItemComponentFactoryFactory
                ?? static fn(callable $configuredServiceFactory): callable => new matcher_item_component_factory($configuredServiceFactory)
        );
        $this->tabDelegateFactoryFactory = \Closure::fromCallable(
            $tabDelegateFactoryFactory
                ?? static fn(callable $configuredServiceFactory): callable => new tab_delegate_factory($configuredServiceFactory)
        );
        $this->tabViewParserFactoryFactory = \Closure::fromCallable(
            $tabViewParserFactoryFactory
                ?? static fn(callable $configuredServiceFactory): callable => new tab_view_parser_factory($configuredServiceFactory)
        );
        $this->plainControllerFactoryFactory = \Closure::fromCallable(
            $plainControllerFactoryFactory
                ?? static fn(callable $configuredServiceFactory): callable => new plain_controller_factory($configuredServiceFactory)
        );
        $this->blockFactoryFactory = \Closure::fromCallable(
            $blockFactoryFactory
                ?? static fn(callable $configuredServiceFactory): callable => new block_factory($configuredServiceFactory)
        );
        $this->blockExceptionFactoryFactory = \Closure::fromCallable(
            $blockExceptionFactoryFactory
                ?? static fn(callable $configuredServiceFactory): callable => new block_exception_factory($configuredServiceFactory)
        );
    }

    public function __invoke(): application_service_sub_factory_defaults_provider
    {
        return new application_service_sub_factory_defaults_provider(
            $this->matcherItemFactoryFactory,
            $this->matcherItemComponentFactoryFactory,
            $this->tabDelegateFactoryFactory,
            $this->tabViewParserFactoryFactory,
            $this->plainControllerFactoryFactory,
            $this->blockFactoryFactory,
            $this->blockExceptionFactoryFactory
        );
    }
}
