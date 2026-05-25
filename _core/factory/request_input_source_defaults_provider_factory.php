<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\adapter\request_input_globals_factory;
use fan\core\runtime\request_input_source_defaults_factory;
use fan\core\runtime\request_input_source_factory;
use fan\core\adapter\request_input_globals;
use fan\core\service\request_input_source;


final class request_input_source_defaults_provider_factory
{
    private \Closure $sourceFactoryProvider;
    private \Closure $globalsFactoryProvider;

    public function __construct(
        ?callable $sourceFactoryProvider = null,
        ?callable $globalsFactoryProvider = null
    ) {
        $this->sourceFactoryProvider = \Closure::fromCallable(
            $sourceFactoryProvider
                ?? static fn(callable $globalsFactory): callable => new request_input_source_factory($globalsFactory)
        );
        $this->globalsFactoryProvider = \Closure::fromCallable(
            $globalsFactoryProvider
                ?? static fn(callable $environmentFactory): callable => new request_input_globals_factory($environmentFactory)
        );
    }

    public function __invoke(callable $environmentFactory): request_input_source_defaults_factory
    {
        $sourceFactoryProvider = $this->sourceFactoryProvider;
        $globalsFactoryProvider = $this->globalsFactoryProvider;

        return new request_input_source_defaults_factory(
            static fn(): callable => $sourceFactoryProvider(
                $globalsFactoryProvider($environmentFactory)
            )
        );
    }
}
