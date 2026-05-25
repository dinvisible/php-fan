<?php

declare(strict_types=1);

use fan\core\base\model\entity;
use FanTest\_core\SourceFileContractTestCase;

class BaseModelEntityTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/base/model/entity.php';

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
        $this->assertStringContainsString('private \Closure $namespaceResolver;', $code);
        $this->assertStringContainsString('private ?object $reflectionClassFactory = null;', $code);
        $this->assertStringContainsString('?callable $namespaceResolver = null', $code);
        $this->assertStringContainsString('?object $reflectionClassFactory = null', $code);
        $this->assertStringContainsString('$this->namespaceResolver = $this->defaultNamespaceResolver();', $code);
        $this->assertStringContainsString('$ns     = $this->namespaceName($this, 2);', $code);
        $this->assertStringContainsString('private function namespaceName(object|string $object, int $depth = 1): string', $code);
        $this->assertStringContainsString('private function namespaceResolver(): callable', $code);
        $this->assertStringContainsString('private function reflectionClass(object|string $className): \ReflectionClass', $code);
        $this->assertStringContainsString('private function defaultNamespaceResolver(): \Closure', $code);
        $this->assertStringContainsString('$this->reflectionClassFactory->create($className)', $code);
        $this->assertStringContainsString('method_exists($this->reflectionClassFactory, \'create\')', $code);
        $this->assertStringNotContainsString('private ?\Closure $namespaceResolver', $code);
        $this->assertStringNotContainsString('private ?\Closure $reflectionClassFactory', $code);
        $this->assertStringNotContainsString('$this->namespaceResolver === null', $code);
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
    }

    public function exposeClassName(string $key): string
    {
        return $this->_getClassName($key);
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
