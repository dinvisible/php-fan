<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\runtime\request_input_defaults_factory;
use fan\core\runtime\request_input_factory;
use fan\core\runtime\request_input_source_defaults_factory;
use fan\core\adapter\request_input_native_environment;
use fan\core\service\request_input;


final class request_input_defaults_provider_factory
{
    private \Closure $environmentFactoryProvider;
    private \Closure $sourceDefaultsProviderFactory;
    private \Closure $requestInputFactoryFactoryProvider;

    public function __construct(
        ?callable $environmentFactoryProvider = null,
        ?callable $sourceDefaultsProviderFactory = null,
        ?callable $requestInputFactoryFactoryProvider = null
    ) {
        $this->environmentFactoryProvider = \Closure::fromCallable(
            $environmentFactoryProvider
                ?? static fn(): object => new request_input_native_environment()
        );
        $this->sourceDefaultsProviderFactory = \Closure::fromCallable(
            $sourceDefaultsProviderFactory
                ?? static fn(callable $environmentFactory): request_input_source_defaults_factory => (new request_input_source_defaults_provider_factory())($environmentFactory)
        );
        $this->requestInputFactoryFactoryProvider = \Closure::fromCallable(
            $requestInputFactoryFactoryProvider
                ?? static fn(callable $sourceFactory): request_input_factory => new request_input_factory($sourceFactory)
        );
    }

    public function __invoke(?callable $environmentFactory = null): request_input_defaults_factory
    {
        $environmentFactory ??= $this->environmentFactoryProvider;
        $sourceDefaultsProviderFactory = $this->sourceDefaultsProviderFactory;
        $sourceDefaults = $sourceDefaultsProviderFactory($environmentFactory);
        $requestInputFactoryFactory = $this->requestInputFactoryFactoryProvider;

        return new request_input_defaults_factory(
            $requestInputFactoryFactory,
            $sourceDefaults->sourceFactory()
        );
    }
}
