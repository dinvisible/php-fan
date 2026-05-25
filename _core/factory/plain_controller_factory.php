<?php

declare(strict_types=1);

namespace fan\core\di;

final class plain_controller_factory
{
    private \Closure $configuredServiceFactory;

    public function __construct(callable $configuredServiceFactory)
    {
        $this->configuredServiceFactory = \Closure::fromCallable($configuredServiceFactory);
    }

    public function __invoke(
        string $controllerClass,
        object $plainService,
        int|string $controllerKey,
        array $dependencies
    ): object {
        return ($this->configuredServiceFactory)($controllerClass, [
            $plainService,
            $controllerKey,
            ...$dependencies
        ]);
    }

}
