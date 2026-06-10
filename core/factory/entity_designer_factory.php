<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\base\model\entity;


final class entity_designer_factory
{
    private \Closure $configuredServiceFactory;

    public function __construct(callable $configuredServiceFactory)
    {
        $this->configuredServiceFactory = \Closure::fromCallable($configuredServiceFactory);
    }

    public function __invoke(
        string $designerClass,
        entity $entity,
        ?callable $classNameResolver = null
    ): object
    {
        $arguments = [$entity];
        if ($classNameResolver !== null) {
            $arguments[] = $classNameResolver;
        }

        return ($this->configuredServiceFactory)($designerClass, $arguments);
    }

}
