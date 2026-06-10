<?php

declare(strict_types=1);

use fan\core\service\user\entity;
use FanTest\core\SourceFileContractTestCase;
use FanTest\core\ConfigRowFactory;
use fan\core\base\model\row as model_row;
use fan\core\service\config\row;
use fan\core\service\user;


if (!function_exists('array_val')) {
    function array_val(array|\ArrayAccess $arr, mixed $key, mixed $default = null): mixed
    {
        if ($key === null) {
            return $default;
        }

        return isset($arr[$key]) ? $arr[$key] : $default;
    }
}

if (!function_exists('adduceToArray')) {
    function adduceToArray(mixed $src): array
    {
        if (empty($src)) {
            return [];
        }

        return match (gettype($src)) {
            'array' => $src,
            'object' => method_exists($src, 'toArray') ? $src->toArray() : (array)$src,
            'integer', 'double', 'string' => [$src],
            default => [],
        };
    }
}

if (!function_exists('fan\core\base\get_class_alt')) {
    eval('
        namespace fan\core\base;

        function get_class_alt(mixed $value): ?string
        {
            return is_object($value) ? get_class($value) : null;
        }
    ');
}

class ServiceUserEntityTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/service/user/entity.php';

    public function testPasswordHashUsesEntityEngineKey(): void
    {
        $engine = new ServiceUserEntityProbe('alice');
        $engine->setConfig(new row(['ENGINE_KEY' => 'pepper']));

        $this->assertSame(md5('alicesecretpepper'), $engine->makePasswordHash('secret'));

        $engine->setLogin('bob');

        $this->assertSame(md5('bobsecretpepper'), $engine->makePasswordHash('secret'));
    }

    public function testEntityDataUsesGettingMapAndRequiredKeys(): void
    {
        $row = new ServiceUserEntityRowDouble([
            'id' => 'getUserId',
            'password' => 'getPasswordHash',
            'login' => 'getLoginName',
            'roles' => 'getRoleList',
            'email' => 'getEmailAddress',
        ]);
        $engine = new ServiceUserEntityProbe('alice');
        $engine->setRow($row);

        $this->assertSame([
            'id' => 42,
            'password' => 'hash',
            'login' => 'alice',
            'roles' => ['admin' => null],
            'email' => 'alice@example.test',
        ], $engine->entityData());
    }

    public function testEntityDataCreatesMissingRequiredKeysExceptionThroughFacade(): void
    {
        $facade = new ServiceUserEntityFacadeDouble();
        $row = new ServiceUserEntityRowDouble([
            'id' => 'getUserId',
            'login' => 'getLoginName',
        ]);
        $engine = (new ServiceUserEntityProbe('alice'))->setFacade($facade);
        $engine->setRow($row);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Required keys "id", "password", "login", "roles" are not get by method "getGettingMap".');

        try {
            $engine->entityData();
        } finally {
            $this->assertSame([
                ['Required keys "id", "password", "login", "roles" are not get by method "getGettingMap".', E_USER_ERROR, null],
            ], $facade->fatalExceptionCalls);
        }
    }

    public function testSaveDataAppliesChangedValuesThroughSettingMapAndSavesRow(): void
    {
        $row = new ServiceUserEntityRowDouble(settingMap: [
            'email' => 'setEmailAddress',
            'status' => 'setStatusValue',
        ]);
        $engine = new ServiceUserEntityProbe('alice');
        $engine->setRow($row);
        $engine->setEmail('alice@example.test');
        $engine->setStatus('active');

        $this->assertTrue($engine->saveData());
        $this->assertSame([
            ['setEmailAddress', 'alice@example.test'],
            ['setStatusValue', 'active'],
        ], $row->setterCalls);
        $this->assertSame(1, $row->saveCalls);
    }

    public function testLoadDataUsesInjectedEntityFactory(): void
    {
        $row = new ServiceUserEntityRowDouble();
        $model = new ServiceUserEntityModelDouble(rowByParam: $row);
        $entityService = new ServiceUserEntityServiceDouble(['users' => $model]);
        $engine = (new ServiceUserEntityProbe('alice'))
            ->setEntityFactory(fn(): ServiceUserEntityServiceDouble => $entityService)
            ->setConfig(ConfigRowFactory::row([
                'ENGINE_KEY' => 'users',
                'IDENTIFYERS' => ['login'],
            ]));

        $this->assertTrue($engine->loadData());
        $this->assertSame([
            ['get', 'users'],
        ], $entityService->calls);
        $this->assertSame([
            ['getRowByParam', ['login' => 'alice']],
        ], $model->calls);
    }

    public function testGetRowUsesInjectedEntityFactoryForNewAndExistingRows(): void
    {
        $newRow = new ServiceUserEntityRowDouble();
        $loadedRow = new ServiceUserEntityRowDouble();
        $model = new ServiceUserEntityModelDouble(newRow: $newRow, rowById: $loadedRow);
        $engine = (new ServiceUserEntityProbe('alice'))
            ->setEntityFactory(fn(): ServiceUserEntityServiceDouble => new ServiceUserEntityServiceDouble(['users' => $model]))
            ->setConfig(new row(['ENGINE_KEY' => 'users']));

        $this->assertSame($newRow, $engine->row());

        $existing = (new ServiceUserEntityProbe('alice'))
            ->setEntityFactory(fn(): ServiceUserEntityServiceDouble => new ServiceUserEntityServiceDouble(['users' => $model]))
            ->setConfig(new row(['ENGINE_KEY' => 'users']));
        $existing->setData(['id' => 42]);
        $existing->markExisting();

        $this->assertSame($loadedRow, $existing->row());
        $this->assertSame([
            ['getNewRow'],
            ['getRowById', 42],
        ], $model->calls);
    }

    public function testSourceUsesInjectedEntityFactoryInsteadOfFacadeLookup(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('setEntityFactory', $source);
        $this->assertStringContainsString('($this->entityFactory)();', $source);
        $this->assertStringContainsString('$this->createUserFatalException(', $source);
        $this->assertStringNotContainsString('new fatalException', $source);
        $this->assertStringNotContainsString('use fan\project\exception\service\fatal as fatalException;', $source);
        $this->assertStringNotContainsString('getContainerService(', $source);
        $this->assertStringNotContainsString('containerService(', $source);
        $this->assertStringNotContainsString('call_user_func($this->entityFactory', $source);
    }
}

final class ServiceUserEntityProbe extends entity
{
    public function __construct(mixed $identifyer)
    {
        parent::__construct(
            $identifyer,
            null,
            null,
            static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default,
            static fn(mixed $value): array => is_array($value) ? $value : (method_exists($value, 'toArray') ? $value->toArray() : (array)$value),
            static fn(object $object): string => get_class($object)
        );
    }

    public function setRow(ServiceUserEntityRowDouble $row): void
    {
        $this->row = $row;
    }

    public function entityData(): array
    {
        return $this->_getEntityData();
    }

    public function saveData(): bool
    {
        return $this->_saveData();
    }

    public function loadData(): bool
    {
        return $this->_loadData();
    }

    public function row(): ?model_row
    {
        return $this->_getRow();
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function markExisting(): void
    {
        $this->isNew = false;
    }
}

final class ServiceUserEntityServiceDouble
{
    public array $calls = [];

    /**
     * @param array<string, ServiceUserEntityModelDouble> $models
     */
    public function __construct(private array $models)
    {
    }

    public function get(string $name): ServiceUserEntityModelDouble
    {
        $this->calls[] = [__FUNCTION__, $name];

        return $this->models[$name];
    }
}

final class ServiceUserEntityModelDouble
{
    public array $calls = [];

    public function __construct(
        private ?ServiceUserEntityRowDouble $rowByParam = null,
        private ?ServiceUserEntityRowDouble $newRow = null,
        private ?ServiceUserEntityRowDouble $rowById = null,
    ) {
    }

    public function getRowByParam(array $param): ServiceUserEntityRowDouble
    {
        $this->calls[] = [__FUNCTION__, $param];

        return $this->rowByParam ?? new ServiceUserEntityRowDouble(loaded: false);
    }

    public function getNewRow(): ServiceUserEntityRowDouble
    {
        $this->calls[] = [__FUNCTION__];

        return $this->newRow ?? new ServiceUserEntityRowDouble();
    }

    public function getRowById(mixed $id): ServiceUserEntityRowDouble
    {
        $this->calls[] = [__FUNCTION__, $id];

        return $this->rowById ?? new ServiceUserEntityRowDouble();
    }
}

final class ServiceUserEntityFacadeDouble extends user
{
    public array $fatalExceptionCalls = [];

    public function __construct()
    {
    }

    public function createUserFatalException(string $message, int $code = E_USER_ERROR, ?Throwable $previous = null): Throwable
    {
        $this->fatalExceptionCalls[] = [$message, $code, $previous];

        return new RuntimeException($message, $code, $previous);
    }
}

final class ServiceUserEntityRowDouble extends model_row
{
    public array $setterCalls = [];

    public int $saveCalls = 0;

    public function __construct(
        private array $gettingMap = [],
        private array $settingMap = [],
        private bool $loaded = true,
    ) {
    }

    public function checkIsLoad(): bool
    {
        return $this->loaded;
    }

    public function getGettingMap(string $type): array
    {
        return $this->gettingMap;
    }

    public function getSettingMap(string $type): array
    {
        return $this->settingMap;
    }

    public function getUserId(): int
    {
        return 42;
    }

    public function getPasswordHash(): string
    {
        return 'hash';
    }

    public function getLoginName(): string
    {
        return 'alice';
    }

    public function getRoleList(): array
    {
        return ['admin' => null];
    }

    public function getEmailAddress(): string
    {
        return 'alice@example.test';
    }

    public function setEmailAddress(string $email): void
    {
        $this->setterCalls[] = [__FUNCTION__, $email];
    }

    public function setStatusValue(string $status): void
    {
        $this->setterCalls[] = [__FUNCTION__, $status];
    }

    public function save(): static
    {
        $this->saveCalls++;

        return $this;
    }
}
