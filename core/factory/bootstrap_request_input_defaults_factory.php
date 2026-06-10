<?php

declare(strict_types=1);

namespace fan\core\di;

final class bootstrap_request_input_defaults_factory
{
    private \Closure $requestInputFactoryFactory;

    public function __construct(?callable $requestInputFactoryFactory = null)
    {
        $this->requestInputFactoryFactory = \Closure::fromCallable(
            $requestInputFactoryFactory
                ?? static function (): callable {
                    $requestInputDefaults = (new request_input_defaults_provider_factory())();

                    return $requestInputDefaults();
                }
        );
    }

    public function requestInputFactory(): callable
    {
        return ($this->requestInputFactoryFactory)();
    }
}
