<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_service_sub_factory_defaults_provider
{
    private \Closure $matcherItemFactoryFactory;
    private \Closure $matcherItemComponentFactoryFactory;
    private \Closure $tabDelegateFactoryFactory;
    private \Closure $tabViewParserFactoryFactory;
    private \Closure $plainControllerFactoryFactory;
    private \Closure $blockFactoryFactory;
    private \Closure $blockExceptionFactoryFactory;

    public function __construct(
        callable $matcherItemFactoryFactory,
        callable $matcherItemComponentFactoryFactory,
        callable $tabDelegateFactoryFactory,
        callable $tabViewParserFactoryFactory,
        callable $plainControllerFactoryFactory,
        callable $blockFactoryFactory,
        callable $blockExceptionFactoryFactory
    ) {
        $this->matcherItemFactoryFactory = \Closure::fromCallable($matcherItemFactoryFactory);
        $this->matcherItemComponentFactoryFactory = \Closure::fromCallable($matcherItemComponentFactoryFactory);
        $this->tabDelegateFactoryFactory = \Closure::fromCallable($tabDelegateFactoryFactory);
        $this->tabViewParserFactoryFactory = \Closure::fromCallable($tabViewParserFactoryFactory);
        $this->plainControllerFactoryFactory = \Closure::fromCallable($plainControllerFactoryFactory);
        $this->blockFactoryFactory = \Closure::fromCallable($blockFactoryFactory);
        $this->blockExceptionFactoryFactory = \Closure::fromCallable($blockExceptionFactoryFactory);
    }

    public function matcherItemFactory(): callable
    {
        return $this->matcherItemFactoryFactory;
    }

    public function matcherItemComponentFactory(): callable
    {
        return $this->matcherItemComponentFactoryFactory;
    }

    public function tabDelegateFactory(): callable
    {
        return $this->tabDelegateFactoryFactory;
    }

    public function tabViewParserFactory(): callable
    {
        return $this->tabViewParserFactoryFactory;
    }

    public function plainControllerFactory(): callable
    {
        return $this->plainControllerFactoryFactory;
    }

    public function blockFactory(): callable
    {
        return $this->blockFactoryFactory;
    }

    public function blockExceptionFactory(): callable
    {
        return $this->blockExceptionFactoryFactory;
    }
}
