<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\bootstrap\application;

final class bootstrap_application_defaults_factory
{
    private \Closure $applicationDefaultsFactoryFactory;
    private \Closure $applicationFactory;

    public function __construct(?callable $applicationDefaultsFactoryFactory = null, ?callable $applicationFactory = null)
    {
        $this->applicationDefaultsFactoryFactory = \Closure::fromCallable(
            $applicationDefaultsFactoryFactory
                ?? static fn(callable $applicationFactory): application_defaults_factory => new application_defaults_factory($applicationFactory)
        );
        $this->applicationFactory = \Closure::fromCallable(
            $applicationFactory
                ?? static fn(): application => new application()
        );
    }

    public function applicationDefaults(): application_defaults_factory
    {
        return ($this->applicationDefaultsFactoryFactory)($this->applicationFactory);
    }

    public function application(): application
    {
        return ($this->applicationFactory)();
    }
}
