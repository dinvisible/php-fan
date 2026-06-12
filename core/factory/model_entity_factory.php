<?php

declare(strict_types=1);

namespace fan\core\di;

final class model_entity_factory
{
    private \Closure $configuredServiceFactory;

    private \Closure $modelEntityExceptionFactory;

    private ?object $reflectionClassFactory = null;

    private mixed $entityIdDecoder = null;

    public function __construct(
        callable $configuredServiceFactory,
        callable $modelEntityExceptionFactory,
        ?object $reflectionClassFactory = null,
        ?callable $entityIdDecoder = null
    )
    {
        $this->configuredServiceFactory = \Closure::fromCallable($configuredServiceFactory);
        $this->modelEntityExceptionFactory = \Closure::fromCallable($modelEntityExceptionFactory);
        $this->reflectionClassFactory = $reflectionClassFactory;
        $this->entityIdDecoder = $entityIdDecoder === null ? null : \Closure::fromCallable($entityIdDecoder);
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
        $entityIdDecoder = $this->entityIdDecoder
            ?? static fn(string $rowId): mixed => $entityService->getEncapsulant()->decryptId($rowId);
        $entityLookup = static fn(string $tableName, ?string $connectionName = null): mixed => $entityService->getEntityByTable($tableName, $connectionName);
        $designerFactory = static fn(object $entity, string $type = 'select'): object => $entityService->getDesigner($entity, $type);
        $descriptionProvider = static fn(object $entity, array $param = []): object => $entityService->getDescription($entity, $param);
        $namespacePrefixResolver = static fn(object $entity): string => $entityService->getNsPrefix();
        $collectionKeyProvider = static fn(object $entity): mixed => $entityService->getCollectionKey();
        $sqlDirectoryProvider = static fn(object $entity): string => $entityService->getSqlDir();

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
            $this->reflectionClassFactory,
            $entityIdDecoder,
            $entityLookup,
            $designerFactory,
            $descriptionProvider,
            $namespacePrefixResolver,
            $collectionKeyProvider,
            $sqlDirectoryProvider
        ]);
    }

}
