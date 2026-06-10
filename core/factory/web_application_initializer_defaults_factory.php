<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\bootstrap\web_application_initializer;

final class web_application_initializer_defaults_factory
{
    private \Closure $webApplicationInitializerFactory;

    public function __construct(?callable $webApplicationInitializerFactory = null)
    {
        if ($webApplicationInitializerFactory === null) {
            $requestRunnerDefaults = new request_runner_defaults_factory();
            $webApplicationInitializerFactory = static fn(): web_application_initializer => new web_application_initializer(
                $requestRunnerDefaults(),
                null,
                dirname(__DIR__, 2) . '/htdocs'
            );
        }

        $this->webApplicationInitializerFactory = \Closure::fromCallable($webApplicationInitializerFactory);
    }

    public function __invoke(): web_application_initializer
    {
        return ($this->webApplicationInitializerFactory)();
    }
}
