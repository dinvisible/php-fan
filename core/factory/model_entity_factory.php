<?php

declare(strict_types=1);

namespace fan\core\di;

final class model_entity_factory
{
    private \Closure $configuredServiceFactory;

    private \Closure $modelEntityExceptionFactory;

    private ?object $reflectionClassFactory = null;

    public function __construct(
        callable $configuredServiceFactory,
        callable $modelEntityExceptionFactory,
        ?object $reflectionClassFactory = null
    )
    {
        $this->configuredServiceFactory = \Closure::fromCallable($configuredServiceFactory);
        $this->modelEntityExceptionFactory = \Closure::fromCallable($modelEntityExceptionFactory);
        $this->reflectionClassFactory = $reflectionClassFactory;
    }

    public function __invoke(
        string $entityClass,
        object $entityService,
        ?string $name,
        array $param,
        ?callable $entityConfigFactory,
        ?callable $databaseFactory,
        ?callable $reflectorFactory,
        ?callable $rowFactory,
        ?callable $rowsetFactory,
        ?callable $requestLoaderFactory,
        ?callable $namespaceResolver = null
    ): object {
        return ($this->configuredServiceFactory)($entityClass, [
            $entityService,
            $name,
            $param,
            $entityConfigFactory,
            $databaseFactory,
            $reflectorFactory,
            $rowFactory,
            $rowsetFactory,
            $requestLoaderFactory,
            $this->modelEntityExceptionFactory,
            $namespaceResolver,
            $this->reflectionClassFactory
        ]);
    }

}
