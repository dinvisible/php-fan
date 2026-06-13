<?php

declare(strict_types=1);

namespace fan\core\base\model;

final class entity_dependencies
{
    public readonly ?\Closure $namespaceResolver;
    public readonly ?object $reflectionClassFactory;
    public readonly ?\Closure $entityIdDecoder;
    public readonly ?\Closure $entityLookup;
    public readonly ?\Closure $designerFactory;
    public readonly ?\Closure $descriptionProvider;
    public readonly ?\Closure $namespacePrefixResolver;
    public readonly ?\Closure $collectionKeyProvider;
    public readonly ?\Closure $sqlDirectoryProvider;
    public readonly ?\Closure $rowDependenciesProvider;
    public readonly ?\Closure $fileDataRowDependenciesProvider;
    public readonly ?\Closure $specFileImageRowDependenciesProvider;
    public readonly ?\Closure $relatedEntityRowFactory;
    public readonly ?\Closure $modelClassExists;

    public function __construct(
        ?callable $namespaceResolver = null,
        ?object $reflectionClassFactory = null,
        ?callable $entityIdDecoder = null,
        ?callable $entityLookup = null,
        ?callable $designerFactory = null,
        ?callable $descriptionProvider = null,
        ?callable $namespacePrefixResolver = null,
        ?callable $collectionKeyProvider = null,
        ?callable $sqlDirectoryProvider = null,
        ?callable $rowDependenciesProvider = null,
        ?callable $fileDataRowDependenciesProvider = null,
        ?callable $specFileImageRowDependenciesProvider = null,
        ?callable $relatedEntityRowFactory = null,
        ?callable $modelClassExists = null
    )
    {
        $this->namespaceResolver = self::nullableClosure($namespaceResolver);
        $this->reflectionClassFactory = $reflectionClassFactory;
        $this->entityIdDecoder = self::nullableClosure($entityIdDecoder);
        $this->entityLookup = self::nullableClosure($entityLookup);
        $this->designerFactory = self::nullableClosure($designerFactory);
        $this->descriptionProvider = self::nullableClosure($descriptionProvider);
        $this->namespacePrefixResolver = self::nullableClosure($namespacePrefixResolver);
        $this->collectionKeyProvider = self::nullableClosure($collectionKeyProvider);
        $this->sqlDirectoryProvider = self::nullableClosure($sqlDirectoryProvider);
        $this->rowDependenciesProvider = self::nullableClosure($rowDependenciesProvider);
        $this->fileDataRowDependenciesProvider = self::nullableClosure($fileDataRowDependenciesProvider);
        $this->specFileImageRowDependenciesProvider = self::nullableClosure($specFileImageRowDependenciesProvider);
        $this->relatedEntityRowFactory = self::nullableClosure($relatedEntityRowFactory);
        $this->modelClassExists = \Closure::fromCallable(
            $modelClassExists ?? static fn(string $className): bool => class_exists($className)
        );
    }

    private static function nullableClosure(?callable $callable): ?\Closure
    {
        return $callable === null ? null : \Closure::fromCallable($callable);
    }
}
