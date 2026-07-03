<?php

declare(strict_types=1);

use fan\core\base\model\entity;
use fan\core\base\model\entity_dependencies;
use FanTest\core\SourceFileContractTestCase;

class BaseModelEntityTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/base/model/entity.php';

    public function testConnectionNameKeyAndMainParamExposeEntityIdentity(): void
    {
        $entity = new BaseModelEntityProbe('users', 'users_table');
        $entity->setConnectionName('main')->setConnectionKey('read');

        $this->assertSame('main', $entity->getConnectionName());
        $this->assertSame('read', $entity->getConnectionKey());
        $this->assertSame([
            'collection' => 'default',
            'name' => 'users',
            'class' => BaseModelEntityProbe::class,
            'param' => ['seed' => true],
            'connection' => [
                'name' => 'main',
                'key' => 'read',
            ],
        ], $entity->getMainParam());
    }

    public function testGetParamByIdUsesScalarPrimaryKeyAndOptionalDecrypt(): void
    {
        $entity = new BaseModelEntityProbe('users', 'users_table', primaryKey: 'id');

        $this->assertSame(['id' => 15], $entity->getParamById(15));
        $this->assertSame(['id' => 24], $entity->getParamById('encrypted-24', true));
    }

    public function testDecodeEntityIdUsesInjectedDecoderWhenConfigured(): void
    {
        $entity = new BaseModelEntityProbe('users', 'users_table', primaryKey: 'id');
        $calls = [];
        $entity->setEntityDependencies(
            entityIdDecoder: static function (string $rowId) use (&$calls): int {
                $calls[] = $rowId;

                return 77;
            }
        );

        $this->assertSame(77, $entity->decodeEntityId('encrypted-77'));
        $this->assertSame(['id' => 77], $entity->getParamById('encrypted-77', true));
        $this->assertSame(['encrypted-77', 'encrypted-77'], $calls);
    }

    public function testFindEntityByTableUsesInjectedLookup(): void
    {
        $entity = new BaseModelEntityProbe('users', 'users_table', primaryKey: 'id');
        $linkedEntity = new stdClass();
        $calls = [];
        $entity->setEntityDependencies(
            entityLookup: static function (string $tableName, ?string $connectionName = null) use (&$calls, $linkedEntity): object {
                $calls[] = [$tableName, $connectionName];

                return $linkedEntity;
            }
        );

        $this->assertSame($linkedEntity, $entity->findEntityByTable('roles', 'main'));
        $this->assertSame([['roles', 'main']], $calls);
    }

    public function testDesignerDescriptionPrefixAndCollectionUseInjectedCollaborators(): void
    {
        $entity = new BaseModelEntityProbe('users', 'users_table', primaryKey: 'id');
        $entity->resetDescriptionForTest();
        $designer = new stdClass();
        $description = new BaseModelEntityDescriptionDouble('id');
        $calls = [];
        $entity->setEntityDependencies(
            designerFactory: static function (entity $modelEntity, string $type) use (&$calls, $entity, $designer): object {
                $calls[] = ['designer', $modelEntity, $type];

                return $designer;
            },
            descriptionProvider: static function (entity $modelEntity, array $param) use (&$calls, $entity, $description): object {
                $calls[] = ['description', $modelEntity, $param];

                return $description;
            },
            namespacePrefixResolver: static function (entity $modelEntity) use (&$calls, $entity): string {
                $calls[] = ['prefix', $modelEntity];

                return '\Injected\\';
            },
            collectionKeyProvider: static function (entity $modelEntity) use (&$calls, $entity): string {
                $calls[] = ['collection', $modelEntity];

                return 'injected-collection';
            },
            sqlDirectoryProvider: static function (entity $modelEntity) use (&$calls, $entity): string {
                $calls[] = ['sql-dir', $modelEntity];

                return '/tmp/sql';
            },
            reflectionClassFactory: new BaseModelEntityReflectionClassFactoryDouble(entity::class)
        );

        $this->assertSame($designer, $entity->getDesigner('update'));
        $this->assertSame($description, $entity->getDescription(['force' => true]));
        $this->assertSame('injected-collection', $entity->getMainParam()['collection']);
        $this->assertSame('/tmp/sql', $entity->getSqlDirectory());
        $this->assertSame('\fan\project\base\model\entity', $entity->exposeClassName('entity'));
        $this->assertSame(
            [
                ['designer', $entity, 'update'],
                ['description', $entity, ['force' => true, 'seed' => true]],
                ['collection', $entity],
                ['sql-dir', $entity],
                ['prefix', $entity],
            ],
            $calls
        );
    }

    public function testRowDependencyProvidersAndRelatedRowFactoryUseInjectedCollaborators(): void
    {
        $entity = new BaseModelEntityProbe('users', 'users_table', primaryKey: 'id');
        $row = new stdClass();
        $calls = [];
        $entity->setEntityDependencies(
            rowDependenciesProvider: static function (entity $modelEntity) use (&$calls, $entity): array {
                $calls[] = ['row-dependencies', $modelEntity];

                return ['row'];
            },
            fileDataRowDependenciesProvider: static function (entity $modelEntity) use (&$calls, $entity): array {
                $calls[] = ['file-data-row-dependencies', $modelEntity];

                return ['file-data'];
            },
            specFileImageRowDependenciesProvider: static function (entity $modelEntity) use (&$calls, $entity): array {
                $calls[] = ['spec-file-image-row-dependencies', $modelEntity];

                return ['spec-image'];
            },
            relatedEntityRowFactory: static function (entity $modelEntity, string $entityName) use (&$calls, $entity, $row): object {
                $calls[] = ['related-row', $modelEntity, $entityName];

                return $row;
            }
        );

        $this->assertSame(['row'], $entity->rowDependencies());
        $this->assertSame(['file-data'], $entity->fileDataRowDependencies());
        $this->assertSame(['spec-image'], $entity->specFileImageRowDependencies());
        $this->assertSame($row, $entity->createRelatedEntityRow('\Project\file_data'));
        $this->assertSame([
            ['row-dependencies', $entity],
            ['file-data-row-dependencies', $entity],
            ['spec-file-image-row-dependencies', $entity],
            ['related-row', $entity, '\Project\file_data'],
        ], $calls);
    }

    public function testEntityDependenciesObjectAppliesNamedCollaborators(): void
    {
        $entity = new BaseModelEntityProbe('users', 'users_table', primaryKey: 'id');
        $row = new stdClass();
        $dependencies = new entity_dependencies(
            entityIdDecoder: static fn(string $rowId): int => 91,
            entityLookup: static fn(string $tableName, ?string $connectionName = null): object => (object)[
                'tableName' => $tableName,
                'connectionName' => $connectionName,
            ],
            collectionKeyProvider: static fn(entity $modelEntity): string => 'named-dependencies',
            rowDependenciesProvider: static fn(entity $modelEntity): array => ['row-from-object'],
            fileDataRowDependenciesProvider: static fn(entity $modelEntity): array => ['file-data-from-object'],
            specFileImageRowDependenciesProvider: static fn(entity $modelEntity): array => ['spec-image-from-object'],
            relatedEntityRowFactory: static fn(entity $modelEntity, string $entityName): object => $row
        );

        $entity->setEntityDependencies(entityDependencies: $dependencies);

        $linkedEntity = $entity->findEntityByTable('roles', 'main');

        $this->assertSame(['id' => 91], $entity->getParamById('encrypted-91', true));
        $this->assertSame('roles', $linkedEntity->tableName);
        $this->assertSame('main', $linkedEntity->connectionName);
        $this->assertSame('named-dependencies', $entity->getMainParam()['collection']);
        $this->assertSame(['row-from-object'], $entity->rowDependencies());
        $this->assertSame(['file-data-from-object'], $entity->fileDataRowDependencies());
        $this->assertSame(['spec-image-from-object'], $entity->specFileImageRowDependencies());
        $this->assertSame($row, $entity->createRelatedEntityRow('photos'));
    }

    public function testRowDependencyProviderRejectsNonArrayResult(): void
    {
        $entity = new BaseModelEntityProbe('users', 'users_table', primaryKey: 'id');
        $entity->setEntityDependencies(
            rowDependenciesProvider: static fn(entity $modelEntity): string => 'invalid'
        );

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Entity row dependencies provider returned "string".');

        $entity->rowDependencies();
    }

    public function testFileDataRowDependencyProviderRejectsNonArrayResult(): void
    {
        $entity = new BaseModelEntityProbe('users', 'users_table', primaryKey: 'id');
        $entity->setEntityDependencies(
            fileDataRowDependenciesProvider: static fn(entity $modelEntity): int => 15
        );

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Entity file-data row dependencies provider returned "integer".');

        $entity->fileDataRowDependencies();
    }

    public function testSpecFileImageRowDependencyProviderRejectsNonArrayResult(): void
    {
        $entity = new BaseModelEntityProbe('users', 'users_table', primaryKey: 'id');
        $entity->setEntityDependencies(
            specFileImageRowDependenciesProvider: static fn(entity $modelEntity): object => new stdClass()
        );

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Entity spec-file image row dependencies provider returned "stdClass".');

        $entity->specFileImageRowDependencies();
    }

    public function testRelatedEntityRowFactoryRejectsNonObjectResult(): void
    {
        $entity = new BaseModelEntityProbe('users', 'users_table', primaryKey: 'id');
        $entity->setEntityDependencies(
            relatedEntityRowFactory: static fn(entity $modelEntity, string $entityName): array => []
        );

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Related entity row factory returned "array".');

        $entity->createRelatedEntityRow('photos');
    }

    public function testGetCheckKeyHashesTableStatusAndCanReduceOutput(): void
    {
        $connection = new BaseModelEntityConnectionDouble([
            'Rows' => 10,
            'Avg_row_length' => 20,
            'Data_length' => 30,
            'Index_length' => 40,
            'Auto_increment' => 11,
            'Update_time' => 'now',
            'Checksum' => 'sum',
        ]);
        $entity = new BaseModelEntityProbe('users', 'users_table', connection: $connection);

        $full = md5('1020304011nowsum');

        $this->assertSame($full, $entity->getCheckKey());
        $this->assertSame(substr($full, 0, 8), $entity->getCheckKey(8));
        $this->assertSame(substr($full, -6), $entity->getCheckKey(-6));
        $this->assertSame(['users_table', 'users_table', 'users_table'], $connection->tableStatusCalls);
    }

    public function testConstructorAndConnectionUseInjectedFactories(): void
    {
        $configService = new BaseModelEntityConfigServiceDouble(
            new BaseModelEntityConfigRowDouble(['CONNECTION' => 'main'])
        );
        $connection = new BaseModelEntityConnectionDouble();
        $databaseCalls = [];

        $entity = new BaseModelEntityConstructProbe(
            new BaseModelEntityServiceDouble(),
            'users',
            ['tableName' => 'users_table'],
            fn(): BaseModelEntityConfigServiceDouble => $configService,
            function (?string $connectionName = null, mixed $extraKey = 0) use (&$databaseCalls, $connection): BaseModelEntityConnectionDouble {
                $databaseCalls[] = [$connectionName, $extraKey];
                return $connection;
            }
        );

        $this->assertSame('users_table', $entity->getTableName());
        $this->assertSame('main', $entity->getConnectionName());
        $this->assertSame($connection, $entity->getConnection());
        $this->assertSame(['users'], $configService->entityConfigNames);
        $this->assertSame([['main', 0]], $databaseCalls);
    }

    public function testConnectionNameUsesInjectedNamespaceResolver(): void
    {
        $configService = new BaseModelEntityConfigServiceDouble(
            new BaseModelEntityConfigRowDouble(['CONNECTION' => '']),
            new BaseModelEntityConfigRowDouble([
                'DEFAULT_CONNECTION' => 'default',
                'CONNECTIONS' => [
                    'Project\Model' => 'tenant',
                ],
            ])
        );
        $calls = [];

        $entity = new BaseModelEntityConstructProbe(
            new BaseModelEntityServiceDouble(),
            'users',
            ['tableName' => 'users_table'],
            configFactory: fn(): BaseModelEntityConfigServiceDouble => $configService,
            databaseFactory: static fn(?string $connectionName = null, mixed $extraKey = 0): BaseModelEntityConnectionDouble => new BaseModelEntityConnectionDouble(),
            namespaceResolver: static function (object|string $object, int $depth = 1) use (&$calls): string {
                $calls[] = [$object, $depth];

                return 'Project\Model';
            }
        );

        $this->assertSame('tenant', $entity->getConnectionName());
        $this->assertSame([[$entity, 2]], $calls);
    }

    public function testConnectionNameUsesNativeNamespaceFallbackWhenResolverIsNotInjected(): void
    {
        $configService = new BaseModelEntityConfigServiceDouble(
            new BaseModelEntityConfigRowDouble(['CONNECTION' => '']),
            new BaseModelEntityConfigRowDouble([
                'DEFAULT_CONNECTION' => 'default',
                'CONNECTIONS' => [
                    '' => 'tenant',
                ],
            ])
        );

        $entity = new BaseModelEntityConstructProbe(
            new BaseModelEntityServiceDouble(),
            'users',
            ['tableName' => 'users_table'],
            configFactory: fn(): BaseModelEntityConfigServiceDouble => $configService,
            databaseFactory: static fn(?string $connectionName = null, mixed $extraKey = 0): BaseModelEntityConnectionDouble => new BaseModelEntityConnectionDouble()
        );

        $this->assertSame('tenant', $entity->getConnectionName());
    }

    public function testMagicGetUsesInjectedModelEntityExceptionFactory(): void
    {
        $entity = new BaseModelEntityProbe('users', 'users_table');
        $exception = new RuntimeException('model entity fatal');
        $factoryCalls = [];
        $entity->setEntityDependencies(
            modelEntityExceptionFactory: static function (
                string $exceptionClass,
                entity $modelEntity,
                string $message,
                int $code,
                ?Throwable $previous
            ) use (&$factoryCalls, $exception): Throwable {
                $factoryCalls[] = [$exceptionClass, $modelEntity, $message, $code, $previous];

                return $exception;
            }
        );

        try {
            $entity->missing;
            $this->fail('Expected injected model entity exception factory throwable.');
        } catch (RuntimeException $thrown) {
            $this->assertSame($exception, $thrown);
        }

        $this->assertSame([
            [
                '\fan\project\exception\model\entity\fatal',
                $entity,
                'There is impossible to get property "missing".',
                E_USER_ERROR,
                null,
            ],
        ], $factoryCalls);
    }

    public function testGetClassNameUsesInjectedReflectionClassFactory(): void
    {
        $entity = new BaseModelEntityProbe('users', 'users_table');
        $factory = new BaseModelEntityReflectionClassFactoryDouble(entity::class);
        $entity->setEntityDependencies(
            reflectionClassFactory: $factory
        );

        $this->assertSame('\fan\project\base\model\entity', $entity->exposeClassName('entity'));
        $this->assertSame(['\fan\project\base\model\entity'], $factory->calls);
    }

    public function testGetClassNameUsesInjectedModelClassAvailabilityChecker(): void
    {
        $entity = new BaseModelEntityProbe('users', 'users_table');
        $factory = new BaseModelEntityReflectionClassFactoryDouble(entity::class);
        $checkedClasses = [];
        $entity->setEntityDependencies(
            reflectionClassFactory: $factory,
            modelClassExists: static function (string $className) use (&$checkedClasses): bool {
                $checkedClasses[] = $className;

                return true;
            }
        );

        $this->assertSame('\Project\users\entity', $entity->exposeClassName('entity'));
        $this->assertSame(['\Project\users\entity'], $checkedClasses);
        $this->assertSame(['\Project\users\entity'], $factory->calls);
    }

    public function testGetClassNameRejectsInvalidInjectedReflectionClassFactoryResult(): void
    {
        $entity = new BaseModelEntityProbe('users', 'users_table');
        $entity->setEntityDependencies(
            reflectionClassFactory: new BaseModelEntityInvalidReflectionClassFactoryDouble()
        );

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Reflection class factory returned "stdClass".');

        $entity->exposeClassName('entity');
    }

    public function testGetClassNameWithoutReflectionClassFactoryFailsAtInjectedBoundary(): void
    {
        $entity = new BaseModelEntityProbe('users', 'users_table');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Reflection class factory must expose create().');

        $entity->exposeClassName('entity');
    }

    public function testSourceNoLongerCallsContainerServiceDirectly(): void
    {
        $code = $this->sourceCode();
        $dependenciesCode = file_get_contents(dirname(__DIR__, 4) . '/core/base/model/entity_dependencies.php');

        $this->assertIsString($dependenciesCode);

        $this->assertStringNotContainsString('containerService(', $code);
        $this->assertStringNotContainsString('container_aware_trait', $code);
        $this->assertStringNotContainsString('new $class($this, $data', $code);
        $this->assertStringNotContainsString('new $class($this)', $code);
        $this->assertStringNotContainsString('new $className($this, $this->reflectorService())', $code);
        $this->assertStringContainsString('private function createRow(', $code);
        $this->assertStringContainsString('private function createRowset(', $code);
        $this->assertStringContainsString('private function createRequestLoader(', $code);
        $this->assertStringContainsString('private function createModelEntityFatalException(', $code);
        $this->assertStringContainsString('public function createDescriptionFatalException(', $code);
        $this->assertStringContainsString('return $this->createModelEntityFatalException($message, $code, $previous);', $code);
        $this->assertStringContainsString('public function createRowsetFatalException(', $code);
        $this->assertStringContainsString('public function createRequestFatalException(', $code);
        $this->assertStringContainsString('public function createDesignerFatalException(', $code);
        $this->assertStringContainsString('private mixed $modelEntityExceptionFactory = null;', $code);
        $this->assertStringContainsString('private ?\Closure $namespaceResolver = null;', $code);
        $this->assertStringContainsString('private ?object $reflectionClassFactory = null;', $code);
        $this->assertStringContainsString('private mixed $entityIdDecoder = null;', $code);
        $this->assertStringContainsString('private mixed $entityLookup = null;', $code);
        $this->assertStringContainsString('private mixed $designerFactory = null;', $code);
        $this->assertStringContainsString('private mixed $descriptionProvider = null;', $code);
        $this->assertStringContainsString('private mixed $namespacePrefixResolver = null;', $code);
        $this->assertStringContainsString('private mixed $collectionKeyProvider = null;', $code);
        $this->assertStringContainsString('private mixed $sqlDirectoryProvider = null;', $code);
        $this->assertStringContainsString('private mixed $rowDependenciesProvider = null;', $code);
        $this->assertStringContainsString('private mixed $fileDataRowDependenciesProvider = null;', $code);
        $this->assertStringContainsString('private mixed $specFileImageRowDependenciesProvider = null;', $code);
        $this->assertStringContainsString('private mixed $relatedEntityRowFactory = null;', $code);
        $this->assertStringContainsString('private \Closure $modelClassExists;', $code);
        $this->assertStringContainsString('callable|entity_dependencies|null $namespaceResolver = null', $code);
        $this->assertStringContainsString('?object $reflectionClassFactory = null', $code);
        $this->assertStringContainsString('?callable $entityIdDecoder = null', $code);
        $this->assertStringContainsString('?callable $entityLookup = null', $code);
        $this->assertStringContainsString('?callable $designerFactory = null', $code);
        $this->assertStringContainsString('?callable $descriptionProvider = null', $code);
        $this->assertStringContainsString('?callable $namespacePrefixResolver = null', $code);
        $this->assertStringContainsString('?callable $collectionKeyProvider = null', $code);
        $this->assertStringContainsString('?callable $sqlDirectoryProvider = null', $code);
        $this->assertStringContainsString('?callable $rowDependenciesProvider = null', $code);
        $this->assertStringContainsString('?callable $fileDataRowDependenciesProvider = null', $code);
        $this->assertStringContainsString('?callable $specFileImageRowDependenciesProvider = null', $code);
        $this->assertStringContainsString('?callable $relatedEntityRowFactory = null', $code);
        $this->assertStringContainsString('?callable $modelClassExists = null', $code);
        $this->assertStringContainsString('?entity_dependencies $entityDependencies = null', $code);
        $this->assertStringContainsString('private function applyEntityDependencies(entity_dependencies $dependencies): void', $code);
        $this->assertStringContainsString('entityDependencies: new entity_dependencies(', $code);
        $this->assertStringContainsString('public function decodeEntityId(string $rowId): mixed', $code);
        $this->assertStringContainsString('public function findEntityByTable(string $tableName, ?string $connectionName = null): ?object', $code);
        $this->assertStringContainsString('return ($this->entityIdDecoder)($rowId);', $code);
        $this->assertStringContainsString('$this->decodeEntityId((string)$rowId)', $code);
        $this->assertStringContainsString('private function createDesigner(string $type): object', $code);
        $this->assertStringContainsString('private function loadDescription(array $param): object', $code);
        $this->assertStringContainsString('private function entityNamespacePrefix(): string', $code);
        $this->assertStringContainsString('private function entityCollectionKey(): mixed', $code);
        $this->assertStringContainsString('private function entitySqlDirectory(): string', $code);
        $this->assertStringContainsString('public function rowDependencies(): array', $code);
        $this->assertStringContainsString('public function fileDataRowDependencies(): array', $code);
        $this->assertStringContainsString('public function specFileImageRowDependencies(): array', $code);
        $this->assertStringContainsString('public function createRelatedEntityRow(string $entityName): object', $code);
        $this->assertStringContainsString('private function entityDependencyList(mixed $provider, string $label): array', $code);
        $this->assertStringNotContainsString('->getService()->', $code);
        $this->assertStringNotContainsString('string|designer', $code);
        $this->assertStringNotContainsString('instanceof designer', $code);
        $this->assertStringContainsString('$this->namespaceResolver = $this->defaultNamespaceResolver();', $code);
        $this->assertStringContainsString('$ns     = $this->namespaceName($this, 2);', $code);
        $this->assertStringContainsString('private function namespaceName(object|string $object, int $depth = 1): string', $code);
        $this->assertStringContainsString('private function namespaceResolver(): callable', $code);
        $this->assertStringContainsString('private function reflectionClass(object|string $className): \ReflectionClass', $code);
        $this->assertStringContainsString('private function modelClassExists(string $className): bool', $code);
        $this->assertStringContainsString('private function defaultNamespaceResolver(): \Closure', $code);
        $this->assertStringContainsString('$this->reflectionClassFactory->create($className)', $code);
        $this->assertStringContainsString('$this->modelClassExists($className)', $code);
        $this->assertStringContainsString('static fn(string $className): bool => class_exists($className)', $dependenciesCode);
        $this->assertStringContainsString('method_exists($this->reflectionClassFactory, \'create\')', $code);
        $this->assertStringNotContainsString('private mixed $namespaceResolver', $code);
        $this->assertStringNotContainsString('private ?\Closure $reflectionClassFactory', $code);
        $this->assertStringNotContainsString('$this->namespaceResolver === null', $code);
        $this->assertStringNotContainsString('!class_exists($className)', $code);
        $this->assertStringNotContainsString('=> class_exists($className)', $code);
        $this->assertStringNotContainsString('get_ns_name(', $code);
        $this->assertStringNotContainsString('new \ReflectionClass($className)', $code);
        $this->assertStringNotContainsString('new fatalException', $code);
        $this->assertStringNotContainsString('use fan\project\exception\model\entity\fatal as fatalException;', $code);
    }
}

final class BaseModelEntityConstructProbe extends entity
{
}

final class BaseModelEntityProbe extends entity
{
    public function __construct(
        string $name,
        string $tableName,
        string|array $primaryKey = 'id',
        ?BaseModelEntityConnectionDouble $connection = null,
    ) {
        $this->name = $name;
        $this->tableName = $tableName;
        $this->bakParam = ['seed' => true];
        $this->service = new BaseModelEntityServiceDouble();
        $this->description = new BaseModelEntityDescriptionDouble($primaryKey);
        $this->connection = $connection ?? new BaseModelEntityConnectionDouble();
        $this->setEntityDependencies(
            entityIdDecoder: static fn(string $rowId): int => (int)str_replace('encrypted-', '', $rowId),
            collectionKeyProvider: static fn(entity $entity): string => 'default',
            namespacePrefixResolver: static fn(entity $entity): string => '\Project\\',
            sqlDirectoryProvider: static fn(entity $entity): string => '/sql',
            rowDependenciesProvider: static fn(entity $entity): array => [],
            fileDataRowDependenciesProvider: static fn(entity $entity): array => [],
            specFileImageRowDependenciesProvider: static fn(entity $entity): array => []
        );
    }

    public function exposeClassName(string $key): string
    {
        return $this->_getClassName($key);
    }

    public function resetDescriptionForTest(): void
    {
        $this->description = null;
    }
}

final class BaseModelEntityReflectionClassFactoryDouble
{
    public array $calls = [];

    public function __construct(private object|string $reflectionTarget)
    {
    }

    public function create(object|string $object): ReflectionClass
    {
        $this->calls[] = $object;

        return new ReflectionClass($this->reflectionTarget);
    }
}

final class BaseModelEntityInvalidReflectionClassFactoryDouble
{
    public function create(object|string $object): object
    {
        return new stdClass();
    }
}

final class BaseModelEntityDescriptionDouble
{
    public function __construct(private string|array $primaryKey)
    {
    }

    public function getPrimeryKey(): string|array
    {
        return $this->primaryKey;
    }
}

final class BaseModelEntityServiceDouble
{
    protected mixed $collection = null;

    public function __construct()
    {
        $this->collection = 'default';
    }

    public function getEncapsulant(?string $class = null): object
    {
        return new class {
            public function decryptId(string $value): int
            {
                return (int)str_replace('encrypted-', '', $value);
            }
        };
    }

    public function getNsPrefix(): string
    {
        return '\Project\\';
    }

    public function getCollectionKey(): mixed
    {
        return $this->collection;
    }
}

final class BaseModelEntityConnectionDouble
{
    public array $tableStatusCalls = [];

    public function __construct(private array $tableStatus = [])
    {
    }

    public function getTableStatus(string $tableName): mixed
    {
        $this->tableStatusCalls[] = $tableName;

        return $this->tableStatus;
    }
}

final class BaseModelEntityConfigServiceDouble
{
    public array $entityConfigNames = [];

    public function __construct(
        private BaseModelEntityConfigRowDouble $entityConfig,
        private ?BaseModelEntityConfigRowDouble $commonConfig = null
    )
    {
    }

    public function getEntityConfig(entity $entity, mixed $name): BaseModelEntityConfigRowDouble
    {
        $this->entityConfigNames[] = $name;

        return $this->entityConfig;
    }

    public function get(string $name): BaseModelEntityConfigRowDouble
    {
        return $this->commonConfig ?? new BaseModelEntityConfigRowDouble([
            'DEFAULT_CONNECTION' => 'default',
            'CONNECTIONS' => [],
        ]);
    }
}

final class BaseModelEntityConfigRowDouble implements ArrayAccess
{
    public function __construct(private array $data)
    {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->data);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->data[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->data[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->data[$offset]);
    }
}
