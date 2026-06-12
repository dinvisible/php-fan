<?php

declare(strict_types=1);

use fan\core\di\entity_designer_factory;
use fan\core\di\entity_encapsulant_factory;
use fan\core\di\model_entity_factory;
use fan\core\di\model_entity_exception_factory;
use fan\core\di\model_request_factory;
use fan\core\di\model_row_factory;
use fan\core\di\model_row_exception_factory;
use fan\core\di\model_rowset_factory;
use PHPUnit\Framework\TestCase;
use fan\core\base\model\entity;
use fan\core\base\model\rowset;


final class EntityModelFactoriesTest extends TestCase
{
    protected function setUp(): void
    {
        require_once dirname(__DIR__, 3) . '/core/base/data.php';
        require_once dirname(__DIR__, 3) . '/core/base/model/entity.php';
        require_once dirname(__DIR__, 3) . '/core/base/model/rowset.php';
    }

    public function testEntityDesignerFactoryCreatesDesignerForModelEntity(): void
    {
        $modelEntity = new EntityModelFactoriesModelEntityDouble();
        $delegatedClass = null;
        $delegatedArguments = null;
        $factory = new entity_designer_factory(
            static function (string $className, array $arguments) use (&$delegatedClass, &$delegatedArguments): object {
                $delegatedClass = $className;
                $delegatedArguments = $arguments;

                return new $className(...$arguments);
            }
        );
        $designer = $factory(EntityModelFactoriesDesignerDouble::class, $modelEntity);

        $this->assertSame(EntityModelFactoriesDesignerDouble::class, $delegatedClass);
        $this->assertSame([$modelEntity], $delegatedArguments);
        $this->assertInstanceOf(EntityModelFactoriesDesignerDouble::class, $designer);
        $this->assertSame($modelEntity, $designer->modelEntity);
    }

    public function testEntityDesignerFactoryPassesOptionalClassNameResolver(): void
    {
        $modelEntity = new EntityModelFactoriesModelEntityDouble();
        $classNameResolver = static fn(object $object): string => get_class($object);
        $delegatedArguments = null;
        $factory = new entity_designer_factory(
            static function (string $className, array $arguments) use (&$delegatedArguments): object {
                $delegatedArguments = $arguments;

                return new $className(...$arguments);
            }
        );
        $designer = $factory(EntityModelFactoriesDesignerWithResolverDouble::class, $modelEntity, $classNameResolver);

        $this->assertSame([$modelEntity, $classNameResolver], $delegatedArguments);
        $this->assertSame($classNameResolver, $designer->classNameResolver);
    }

    public function testEntityEncapsulantFactoryCreatesEncapsulantForService(): void
    {
        $entityService = new EntityModelFactoriesEntityServiceDouble();
        $delegatedClass = null;
        $delegatedArguments = null;
        $factory = new entity_encapsulant_factory(
            static function (string $className, array $arguments) use (&$delegatedClass, &$delegatedArguments): object {
                $delegatedClass = $className;
                $delegatedArguments = $arguments;

                return new $className(...$arguments);
            }
        );
        $encapsulant = $factory(EntityModelFactoriesEncapsulantDouble::class, $entityService);

        $this->assertSame(EntityModelFactoriesEncapsulantDouble::class, $delegatedClass);
        $this->assertSame([$entityService], $delegatedArguments);
        $this->assertInstanceOf(EntityModelFactoriesEncapsulantDouble::class, $encapsulant);
        $this->assertSame($entityService, $encapsulant->entityService);
    }

    public function testModelEntityFactoryCreatesModelEntityWithDependencies(): void
    {
        $entityService = new EntityModelFactoriesEntityServiceDouble();
        $entityConfigFactory = static fn(): object => new stdClass();
        $databaseFactory = static fn(): object => new stdClass();
        $reflectorFactory = static fn(): object => new stdClass();
        $rowFactory = static fn(): object => new stdClass();
        $rowsetFactory = static fn(): object => new stdClass();
        $requestLoaderFactory = static fn(): object => new stdClass();
        $modelEntityExceptionFactory = static fn(): Throwable => new RuntimeException('model entity fatal');
        $namespaceResolver = static fn(object|string $object, int $depth = 1): string => 'entity-namespace-' . $depth;
        $reflectionClassFactory = new EntityModelFactoriesReflectionClassFactoryDouble();

        $delegatedClass = null;
        $delegatedArguments = null;
        $factory = new model_entity_factory(
            static function (string $className, array $arguments) use (&$delegatedClass, &$delegatedArguments): object {
                $delegatedClass = $className;
                $delegatedArguments = $arguments;

                return new $className(...$arguments);
            },
            $modelEntityExceptionFactory,
            $reflectionClassFactory
        );
        $entity = $factory(
            EntityModelFactoriesConstructedModelEntityDouble::class,
            $entityService,
            'users',
            ['flag' => true],
            $entityConfigFactory,
            $databaseFactory,
            $reflectorFactory,
            $rowFactory,
            $rowsetFactory,
            $requestLoaderFactory,
            $namespaceResolver
        );

        $this->assertSame(EntityModelFactoriesConstructedModelEntityDouble::class, $delegatedClass);
        for ($i = 12; $i <= 17; $i++) {
            $this->assertInstanceOf(Closure::class, $delegatedArguments[$i]);
        }
        $this->assertSame(
            [$entityService, 'users', ['flag' => true], $entityConfigFactory, $databaseFactory, $reflectorFactory, $rowFactory, $rowsetFactory, $requestLoaderFactory, $modelEntityExceptionFactory, $namespaceResolver, $reflectionClassFactory],
            array_slice($delegatedArguments, 0, 12)
        );
        $this->assertInstanceOf(EntityModelFactoriesConstructedModelEntityDouble::class, $entity);
        for ($i = 12; $i <= 17; $i++) {
            $this->assertInstanceOf(Closure::class, $entity->dependencies[$i]);
        }
        $this->assertSame(
            [$entityService, 'users', ['flag' => true], $entityConfigFactory, $databaseFactory, $reflectorFactory, $rowFactory, $rowsetFactory, $requestLoaderFactory, $modelEntityExceptionFactory, $namespaceResolver, $reflectionClassFactory],
            array_slice($entity->dependencies, 0, 12)
        );
        $this->assertSame(15, ($entity->dependencies[12])('encrypted-15'));
        $this->assertSame($entityService->linkedEntity, ($entity->dependencies[13])('roles', 'main'));
        $this->assertSame($entityService->designer, ($entity->dependencies[14])($entityService->linkedEntity, 'update'));
        $this->assertSame($entityService->description, ($entity->dependencies[15])($entityService->linkedEntity, ['force' => true]));
        $this->assertSame('\Project\\', ($entity->dependencies[16])($entityService->linkedEntity));
        $this->assertSame('default', ($entity->dependencies[17])($entityService->linkedEntity));
        $this->assertSame(
            [
                ['getEntityByTable', 'roles', 'main'],
                ['getDesigner', $entityService->linkedEntity, 'update'],
                ['getDescription', $entityService->linkedEntity, ['force' => true]],
                ['getNsPrefix'],
                ['getCollectionKey'],
            ],
            $entityService->calls
        );
    }

    public function testModelEntityExceptionFactoryDelegatesConfiguredConstruction(): void
    {
        $modelEntity = new EntityModelFactoriesModelEntityDouble();
        $previous = new RuntimeException('previous');
        $delegatedClass = null;
        $delegatedArguments = null;
        $factory = new model_entity_exception_factory(
            static function (string $className, array $arguments) use (&$delegatedClass, &$delegatedArguments): object {
                $delegatedClass = $className;
                $delegatedArguments = $arguments;

                return new $className(...$arguments);
            }
        );

        $exception = $factory(EntityModelFactoriesModelEntityFatalDouble::class, $modelEntity, 'broken entity', 501, $previous);

        $this->assertSame(EntityModelFactoriesModelEntityFatalDouble::class, $delegatedClass);
        $this->assertSame([$modelEntity, 'broken entity', 501, $previous], $delegatedArguments);
        $this->assertInstanceOf(EntityModelFactoriesModelEntityFatalDouble::class, $exception);
        $this->assertSame($modelEntity, $exception->modelEntity);
        $this->assertSame('broken entity', $exception->getMessage());
        $this->assertSame(501, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testModelRowFactoryCreatesRowWithSerializerOperations(): void
    {
        $serializerOperations = new EntityModelFactoriesSerializerOperationsDouble();
        $modelEntity = new EntityModelFactoriesModelEntityDouble();
        $data = ['id' => 7];
        $modelRowExceptionFactory = static fn(): Throwable => new RuntimeException('model row fatal');
        $delegatedClass = null;
        $factory = new model_row_factory(
            $serializerOperations,
            static function (string $className, array $arguments) use (&$delegatedClass): object {
                $delegatedClass = $className;

                return new $className(...$arguments);
            },
            $modelRowExceptionFactory
        );

        $row = $factory(
            EntityModelFactoriesRowDouble::class,
            $modelEntity,
            $data
        );

        $this->assertSame(EntityModelFactoriesRowDouble::class, $delegatedClass);
        $this->assertInstanceOf(EntityModelFactoriesRowDouble::class, $row);
        $this->assertSame($modelEntity, $row->modelEntity);
        $this->assertSame(['id' => 7, 'rowTouched' => true], $row->data);
        $this->assertSame(['id' => 7, 'rowTouched' => true], $data);
        $this->assertNull($row->rowset);
        $this->assertSame('encoded', ($row->snapshotEncoder)('value'));
        $this->assertSame('decoded', ($row->snapshotDecoder)('value'));
        $this->assertSame($modelRowExceptionFactory, $row->modelRowExceptionFactory);
    }

    public function testModelRowExceptionFactoryDelegatesConfiguredConstruction(): void
    {
        $modelEntity = new EntityModelFactoriesModelEntityDouble();
        $previous = new RuntimeException('previous row');
        $delegatedClass = null;
        $delegatedArguments = null;
        $factory = new model_row_exception_factory(
            static function (string $className, array $arguments) use (&$delegatedClass, &$delegatedArguments): object {
                $delegatedClass = $className;
                $delegatedArguments = $arguments;

                return new $className(...$arguments);
            }
        );

        $exception = $factory(EntityModelFactoriesModelEntityFatalDouble::class, $modelEntity, 'broken row', 502, $previous);

        $this->assertSame(EntityModelFactoriesModelEntityFatalDouble::class, $delegatedClass);
        $this->assertSame([$modelEntity, 'broken row', 502, $previous], $delegatedArguments);
        $this->assertInstanceOf(EntityModelFactoriesModelEntityFatalDouble::class, $exception);
        $this->assertSame($modelEntity, $exception->modelEntity);
        $this->assertSame('broken row', $exception->getMessage());
        $this->assertSame(502, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testModelRowsetFactoryCreatesRowsetWithSerializerOperations(): void
    {
        $serializerOperations = new EntityModelFactoriesSerializerOperationsDouble();
        $modelEntity = new EntityModelFactoriesModelEntityDouble();
        $data = [['id' => 1]];
        $rowFactory = static fn(): object => new stdClass();
        $delegatedClass = null;
        $factory = new model_rowset_factory(
            $serializerOperations,
            static function (string $className, array $arguments) use (&$delegatedClass): object {
                $delegatedClass = $className;

                return new $className(...$arguments);
            }
        );

        $rowset = $factory(
            EntityModelFactoriesRowsetDouble::class,
            $modelEntity,
            $data,
            $rowFactory
        );

        $this->assertSame(EntityModelFactoriesRowsetDouble::class, $delegatedClass);
        $this->assertInstanceOf(EntityModelFactoriesRowsetDouble::class, $rowset);
        $this->assertSame($modelEntity, $rowset->modelEntity);
        $this->assertSame([['id' => 1], ['rowsetTouched' => true]], $rowset->data);
        $this->assertSame([['id' => 1], ['rowsetTouched' => true]], $data);
        $this->assertSame($rowFactory, $rowset->rowFactory);
        $this->assertSame('encoded', ($rowset->snapshotEncoder)('value'));
        $this->assertSame('decoded', ($rowset->snapshotDecoder)('value'));
    }

    public function testModelRequestFactoryCreatesRequestWithReflector(): void
    {
        $modelEntity = new EntityModelFactoriesModelEntityDouble();
        $reflector = new stdClass();
        $fileStorage = new stdClass();
        $delegatedClass = null;
        $delegatedArguments = null;
        $factory = new model_request_factory(
            static function (string $className, array $arguments) use (&$delegatedClass, &$delegatedArguments): object {
                $delegatedClass = $className;
                $delegatedArguments = $arguments;

                return new $className(...$arguments);
            },
            $fileStorage
        );
        $request = $factory(EntityModelFactoriesRequestDouble::class, $modelEntity, $reflector);

        $this->assertSame(EntityModelFactoriesRequestDouble::class, $delegatedClass);
        $this->assertSame([$modelEntity, $reflector, $fileStorage], $delegatedArguments);
        $this->assertInstanceOf(EntityModelFactoriesRequestDouble::class, $request);
        $this->assertSame($modelEntity, $request->modelEntity);
        $this->assertSame($reflector, $request->reflector);
        $this->assertSame($fileStorage, $request->fileStorage);
    }}

final class EntityModelFactoriesModelEntityDouble extends entity
{
    public function __construct()
    {
    }
}

final class EntityModelFactoriesConstructedModelEntityDouble
{
    public array $dependencies;

    public function __construct(
        object $entityService,
        ?string $name,
        array $param,
        ?callable $entityConfigFactory,
        ?callable $databaseFactory,
        ?callable $reflectorFactory,
        ?callable $rowFactory,
        ?callable $rowsetFactory,
        ?callable $requestLoaderFactory,
        ?callable $modelEntityExceptionFactory,
        ?callable $namespaceResolver = null,
        ?object $reflectionClassFactory = null,
        ?callable $entityIdDecoder = null,
        ?callable $entityLookup = null,
        ?callable $designerFactory = null,
        ?callable $descriptionProvider = null,
        ?callable $namespacePrefixResolver = null,
        ?callable $collectionKeyProvider = null
    ) {
        $this->dependencies = [
            $entityService,
            $name,
            $param,
            $entityConfigFactory,
            $databaseFactory,
            $reflectorFactory,
            $rowFactory,
            $rowsetFactory,
            $requestLoaderFactory,
            $modelEntityExceptionFactory,
            $namespaceResolver,
            $reflectionClassFactory,
            $entityIdDecoder,
            $entityLookup,
            $designerFactory,
            $descriptionProvider,
            $namespacePrefixResolver,
            $collectionKeyProvider,
        ];
    }
}

final class EntityModelFactoriesEntityServiceDouble
{
    public object $linkedEntity;

    public object $designer;

    public object $description;

    public array $calls = [];

    public function __construct()
    {
        $this->linkedEntity = new stdClass();
        $this->designer = new stdClass();
        $this->description = new stdClass();
    }

    public function getEncapsulant(): object
    {
        return new class {
            public function decryptId(string $rowId): int
            {
                return (int)str_replace('encrypted-', '', $rowId);
            }
        };
    }

    public function getEntityByTable(string $tableName, ?string $connectionName = null): object
    {
        $this->calls[] = ['getEntityByTable', $tableName, $connectionName];

        return $this->linkedEntity;
    }

    public function getDesigner(object $entity, string $type): object
    {
        $this->calls[] = ['getDesigner', $entity, $type];

        return $this->designer;
    }

    public function getDescription(object $entity, array $param): object
    {
        $this->calls[] = ['getDescription', $entity, $param];

        return $this->description;
    }

    public function getNsPrefix(): string
    {
        $this->calls[] = ['getNsPrefix'];

        return '\Project\\';
    }

    public function getCollectionKey(): string
    {
        $this->calls[] = ['getCollectionKey'];

        return 'default';
    }
}

final class EntityModelFactoriesReflectionClassFactoryDouble
{
    public function create(object|string $object): ReflectionClass
    {
        return new ReflectionClass($object);
    }
}

final class EntityModelFactoriesModelEntityFatalDouble extends RuntimeException
{
    public function __construct(
        public entity $modelEntity,
        string $message,
        int $code,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}

final class EntityModelFactoriesDesignerDouble
{
    public function __construct(public entity $modelEntity)
    {
    }
}

final class EntityModelFactoriesDesignerWithResolverDouble
{
    public function __construct(
        public entity $modelEntity,
        public $classNameResolver
    ) {
    }
}

final class EntityModelFactoriesEncapsulantDouble
{
    public function __construct(public object $entityService)
    {
    }
}

final class EntityModelFactoriesRowDouble
{
    public array $data;

    public function __construct(
        public entity $modelEntity,
        array &$data = [],
        public ?rowset $rowset = null,
        mixed $serviceContainer = null,
        public mixed $snapshotEncoder = null,
        public mixed $snapshotDecoder = null,
        public mixed $modelRowExceptionFactory = null
    ) {
        $data['rowTouched'] = true;
        $this->data = $data;
    }
}

final class EntityModelFactoriesRowsetDouble
{
    public array $data;

    public function __construct(
        public entity $modelEntity,
        array &$data,
        public $rowFactory,
        public mixed $snapshotEncoder = null,
        public mixed $snapshotDecoder = null
    ) {
        $data[] = ['rowsetTouched' => true];
        $this->data = $data;
    }
}

final class EntityModelFactoriesRequestDouble
{
    public function __construct(
        public entity $modelEntity,
        public object $reflector,
        public object $fileStorage
    )
    {
    }
}

final class EntityModelFactoriesSerializerOperationsDouble
{
    public function phpSnapshotEncoder(): callable
    {
        return static fn(mixed $value): string => 'encoded';
    }

    public function phpSnapshotDecoder(): callable
    {
        return static fn(mixed $value): string => 'decoded';
    }
}
