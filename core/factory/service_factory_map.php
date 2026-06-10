<?php

declare(strict_types=1);

namespace fan\core\di;

final class service_factory_map
{
    private \Closure $configuredServiceFactory;

    public function __construct(callable $configuredServiceFactory)
    {
        $this->configuredServiceFactory = \Closure::fromCallable($configuredServiceFactory);
    }

    public function create(
        string $className,
        string $coreClass,
        array $coreArguments,
        array $extensionArguments
    ): object {
        if ($className === $coreClass) {
            return new $coreClass(...$coreArguments);
        }

        return ($this->configuredServiceFactory)($className, $extensionArguments);
    }
}
