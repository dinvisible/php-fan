<?php

declare(strict_types=1);

use fan\core\base\model\entity;
use fan\core\base\model\row;
use fan\core\base\model\rowset;
use FanTest\core\SourceFileContractTestCase;

class BaseModelRowsetTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/base/model/rowset.php';

    public function testRowsetWrapsRowsAndCanReturnRecursiveArray(): void
    {
        $data = [
            ['id' => 1, 'name' => 'fan'],
            ['id' => 2, 'name' => 'core'],
        ];

        $rowset = new rowset(new BaseModelRowsetEntityDouble(), $data, $this->rowFactory());

        $this->assertContainsOnlyInstancesOf(BaseModelRowsetRowDouble::class, $rowset->toArray());
        $this->assertSame([
            ['id' => 1, 'name' => 'fan'],
            ['id' => 2, 'name' => 'core'],
        ], $rowset->toArray(true));
    }

    public function testRowsCanBeIndexedAndProjectedById(): void
    {
        $data = [
            ['id' => 1, 'name' => 'fan'],
            ['id' => 2, 'name' => 'core'],
        ];

        $rowset = new rowset(new BaseModelRowsetEntityDouble(), $data, $this->rowFactory());

        $rowsById = $rowset->getRowsById();

        $this->assertSame([1, 2], array_keys($rowsById));
        $this->assertSame([1 => 'fan', 2 => 'core'], $rowset->getColumn('name'));
        $this->assertSame([1 => 'fan', 2 => 'core'], $rowset->getArrayHash('id', 'name'));
    }

    public function testNonScalarIdCreatesRowsetExceptionThroughEntityBoundary(): void
    {
        $data = [
            ['id' => 1, 'tenant_id' => 10, 'name' => 'fan'],
        ];
        $entity = new BaseModelRowsetEntityDouble(['tenant_id', 'id']);
        $rowset = new rowset($entity, $data, $this->rowFactory());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Method "getRowsById" allowed only for Scalar Id!');

        try {
            $rowset->getRowsById();
        } finally {
            $this->assertSame([
                ['Method "getRowsById" allowed only for Scalar Id!', E_USER_ERROR, null],
            ], $entity->fatalExceptionCalls);
        }
    }

    public function testManualSnapshotSerializationUsesInjectedCodec(): void
    {
        $data = [
            ['id' => 1, 'name' => 'fan'],
        ];
        $encodedState = null;
        $calls = [];
        $encoder = static function (mixed $state) use (&$encodedState, &$calls): string {
            $encodedState = $state;
            $calls[] = ['encode', array_keys($state)];

            return 'rowset-snapshot';
        };
        $decoder = static function (string $payload, mixed $default = null) use (&$encodedState, &$calls): mixed {
            $calls[] = ['decode', $payload, $default];

            return $encodedState;
        };

        $rowset = new rowset(new BaseModelRowsetEntityDouble(), $data, $this->rowFactory(), $encoder, $decoder);
        $this->assertSame('rowset-snapshot', $rowset->serialize());

        $empty = [];
        $restored = new rowset(new BaseModelRowsetEntityDouble(), $empty, $this->rowFactory(), $encoder, $decoder);
        $restored->unserialize('rowset-snapshot');

        $this->assertSame([
            ['id' => 1, 'name' => 'fan'],
        ], $restored->toArray(true));
        $this->assertSame([
            ['encode', ['multiLevel', 'fullRewrite', 'errMsg', 'data']],
            ['decode', 'rowset-snapshot', null],
        ], $calls);
    }

    public function testSourceUsesInjectedRowFactory(): void
    {
        $code = $this->sourceCode();

        $this->assertStringContainsString('callable $rowFactory', $code);
        $this->assertStringContainsString('$row = $rowFactory($rowClass, $entity, $v, $this);', $code);
        $this->assertStringContainsString('private function createRowsetFatalException(', $code);
        $this->assertStringContainsString('$this->getEntity()->createRowsetFatalException($message, $code, $previous)', $code);
        $this->assertStringNotContainsString('new $rowClass', $code);
        $this->assertStringNotContainsString('new fatalException', $code);
        $this->assertStringNotContainsString('use fan\project\exception\model\entity\fatal as fatalException;', $code);
        $this->assertStringNotContainsString('safe_serializer::', $code);
    }

    private function rowFactory(): callable
    {
        return static fn(string $rowClass, entity $entity, array &$data, rowset $rowset): object => new $rowClass($entity, $data, $rowset);
    }
}

final class BaseModelRowsetEntityDouble extends entity
{
    public array $fatalExceptionCalls = [];

    private BaseModelRowsetDescriptionDouble $descriptionDouble;

    public function __construct(string|array $primaryKey = 'id')
    {
        $this->descriptionDouble = new BaseModelRowsetDescriptionDouble($primaryKey);
    }

    public function getRowClassName(): string
    {
        return BaseModelRowsetRowDouble::class;
    }

    public function getDescription(array $param = []): object
    {
        return $this->descriptionDouble;
    }

    public function createRowsetFatalException(string $message, int $code = E_USER_ERROR, ?Throwable $previous = null): Throwable
    {
        $this->fatalExceptionCalls[] = [$message, $code, $previous];

        return new RuntimeException($message, $code, $previous);
    }
}

final class BaseModelRowsetDescriptionDouble
{
    public function __construct(private string|array $primaryKey = 'id')
    {
    }

    public function getPrimeryKey(): string|array
    {
        return $this->primaryKey;
    }
}

final class BaseModelRowsetRowDouble extends row
{
    public function __construct(entity $entity, array &$data = [], ?rowset $rowset = null)
    {
        $this->entity = $entity;
        $this->rowset = $rowset;
        $this->srcData = $data;
        $this->data = $data;
        $this->isDataLoad = true;
    }

    public function getId(bool $allowException = true, bool $useSourceValue = false, bool $alwaysArray = false): mixed
    {
        return $alwaysArray ? ['id' => $this->data['id']] : $this->data['id'];
    }
}
