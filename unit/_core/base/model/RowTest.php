<?php

declare(strict_types=1);

use fan\core\base\model\entity;
use fan\core\base\model\row;
use FanTest\_core\SourceFileContractTestCase;

class BaseModelRowTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/base/model/row.php';

    public function testSetNormalizesValuesByFieldMetadataAndTracksChanges(): void
    {
        $data = [];
        $row = new row(new BaseModelRowEntityDouble(), $data);

        $row->set('name', 'abcdef');
        $row->set('age', null);

        $this->assertSame('abc', $row->get('name'));
        $this->assertSame(0, $row->get('age'));
        $this->assertSame(['name' => 'abc', 'age' => 0], $row->getChangedElm());
        $this->assertSame(['name' => 'abc', 'age' => 0], $row->toArray());
    }

    public function testLocalizedSetDuplicatesDefaultLocaleForNewRows(): void
    {
        $data = [];
        $row = new row(new BaseModelRowEntityDouble(), $data);
        $row->setRowDependencies(null, fn(): BaseModelRowLocaleDouble => new BaseModelRowLocaleDouble('en', 'de'));

        $row->setByLocal('title', 'Hello');

        $this->assertSame('Hello', $row->get('title_en'));
        $this->assertSame('Hello', $row->get('title_de'));
        $this->assertSame('Hello', $row->getByLocal('title'));
    }

    public function testLoadedRowsExposeSourceDataAndIds(): void
    {
        $data = ['id' => 7, 'name' => 'fan'];
        $row = new row(new BaseModelRowEntityDouble(), $data);

        $this->assertTrue($row->checkIsLoad());
        $this->assertSame(7, $row->getId());
        $this->assertSame(['id' => 7, 'name' => 'fan'], $row->getSrcFields());

        $row->name = 'core';
        $this->assertSame('cor', $row->name);

        $row->revert();
        $this->assertSame('fan', $row->name);
        $this->assertSame([], $row->getChangedElm());
    }

    public function testStringArrayValuesUseInjectedErrorLogger(): void
    {
        $data = [];
        $error = new BaseModelRowErrorDouble();
        $row = new row(new BaseModelRowEntityDouble(), $data);
        $row->setRowDependencies(fn(): BaseModelRowErrorDouble => $error);

        $row->set('name', ['bad']);

        $this->assertSame('', $row->get('name'));
        $this->assertSame(
            [['Value of field "name" can\'t be set as Array', 'Error set value of row', '', true, false]],
            $error->messages
        );
    }

    public function testUnknownFieldUsesInjectedModelRowExceptionFactory(): void
    {
        $data = [];
        $row = new row(new BaseModelRowEntityDouble(), $data);
        $exception = new RuntimeException('model row fatal');
        $factoryCalls = [];
        $row->setRowDependencies(
            modelRowExceptionFactory: static function (
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
            $row->get('missing');
            $this->fail('Expected injected model row exception factory throwable.');
        } catch (RuntimeException $thrown) {
            $this->assertSame($exception, $thrown);
        }

        $this->assertCount(1, $factoryCalls);
        $this->assertSame('\fan\project\exception\model\entity\fatal', $factoryCalls[0][0]);
        $this->assertSame("Call for unset field \"missing\"! \n Exist fields:array (\n)", $factoryCalls[0][2]);
        $this->assertSame(E_USER_ERROR, $factoryCalls[0][3]);
        $this->assertNull($factoryCalls[0][4]);
    }

    public function testManualSnapshotSerializationUsesInjectedCodec(): void
    {
        $data = ['id' => 7, 'name' => 'fan'];
        $encodedState = null;
        $calls = [];
        $encoder = static function (mixed $state) use (&$encodedState, &$calls): string {
            $encodedState = $state;
            $calls[] = ['encode', array_keys($state)];

            return 'row-snapshot';
        };
        $decoder = static function (string $payload, mixed $default = null) use (&$encodedState, &$calls): mixed {
            $calls[] = ['decode', $payload, $default];

            return $encodedState;
        };

        $row = new row(new BaseModelRowEntityDouble(), $data, null, null, $encoder, $decoder);
        $this->assertSame('row-snapshot', $row->serialize());

        $restoreData = [];
        $restored = new row(new BaseModelRowEntityDouble(), $restoreData, null, null, $encoder, $decoder);
        $restored->setRowDependencies(
            entityFactory: static fn(): BaseModelRowEntityServiceDouble => new BaseModelRowEntityServiceDouble()
        );
        $restored->unserialize('row-snapshot');

        $this->assertSame(['id' => 7, 'name' => 'fan'], $restored->toArray());
        $this->assertSame([
            ['encode', ['mainParam', 'srcData', 'data', 'changed', 'isDataLoad', 'initIdOnly']],
            ['decode', 'row-snapshot', []],
        ], $calls);
    }

    public function testMissingSnapshotEncoderFailsAtUseTime(): void
    {
        $data = ['id' => 7, 'name' => 'fan'];
        $row = new row(new BaseModelRowEntityDouble(), $data);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Snapshot encoder is not configured for model row.');

        $row->serialize();
    }

    public function testMissingSnapshotDecoderFailsAtUseTime(): void
    {
        $data = ['id' => 7, 'name' => 'fan'];
        $row = new row(new BaseModelRowEntityDouble(), $data);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Snapshot decoder is not configured for model row.');

        $row->unserialize('row-snapshot');
    }

    public function testGetFieldsUsesInjectedArrayLikeChecker(): void
    {
        $data = ['id' => 7, 'name' => 'fan'];
        $row = new row(new BaseModelRowEntityDouble(), $data);
        $keys = new BaseModelRowFieldKeyList(['id', 'name']);
        $checks = [];
        $row->setRowDependencies(
            arrayLikeChecker: static function (mixed $value) use (&$checks): bool {
                $checks[] = $value;

                return $value instanceof BaseModelRowFieldKeyList;
            }
        );

        $this->assertSame(['id' => 7, 'name' => 'fan'], $row->getFields($keys));
        $this->assertSame([$keys], $checks);
    }

    public function testNamespaceNameUsesInjectedResolver(): void
    {
        $data = ['id' => 7];
        $row = new BaseModelRowNamespaceProbe(new BaseModelRowEntityDouble(), $data);
        $calls = [];
        $row->setRowDependencies(
            namespaceResolver: static function (object|string $object, int $depth = 1) use (&$calls): string {
                $calls[] = [$object, $depth];

                return 'resolved\namespace';
            }
        );

        $this->assertSame('resolved\namespace', $row->exposeNamespaceName($row, 2));
        $this->assertSame([[$row, 2]], $calls);
    }

    public function testNamespaceNameUsesNativeFallbackWhenResolverIsNotInjected(): void
    {
        $data = ['id' => 7];
        $row = new BaseModelRowNamespaceProbe(new BaseModelRowEntityDouble(), $data);

        $this->assertSame('fan\core\base\model', $row->exposeNamespaceName('fan\core\base\model\row', 1));
    }

    public function testSourceNoLongerCallsContainerServiceDirectly(): void
    {
        $code = $this->sourceCode();

        $this->assertStringNotContainsString('containerService(', $code);
        $this->assertStringNotContainsString('container_aware_trait', $code);
        $this->assertStringNotContainsString('safe_serializer::', $code);
        $this->assertStringContainsString('private mixed $modelRowExceptionFactory = null;', $code);
        $this->assertStringContainsString('protected function createModelRowFatalException(', $code);
        $this->assertStringNotContainsString('new fatalException', $code);
        $this->assertStringNotContainsString('use fan\project\exception\model\entity\fatal as fatalException;', $code);
        $this->assertStringContainsString('$lengthFunction = $isUtf8 ? \'mb_strlen\' : \'strlen\';', $code);
        $this->assertStringContainsString('$substringFunction = $isUtf8 ? \'mb_substr\' : \'substr\';', $code);
        $this->assertStringNotContainsString('call_user_func($isUtf8 ? \'mb_strlen\' : \'strlen\'', $code);
        $this->assertStringNotContainsString('call_user_func($isUtf8 ? \'mb_substr\' : \'substr\'', $code);
        $this->assertStringContainsString('private \Closure $snapshotEncoder;', $code);
        $this->assertStringContainsString('private \Closure $snapshotDecoder;', $code);
        $this->assertStringContainsString('$this->snapshotEncoder = \Closure::fromCallable(', $code);
        $this->assertStringContainsString('$this->snapshotDecoder = \Closure::fromCallable(', $code);
        $this->assertStringContainsString('if (!isset($this->snapshotEncoder)) {', $code);
        $this->assertStringContainsString('if (!isset($this->snapshotDecoder)) {', $code);
        $this->assertStringNotContainsString('private mixed $snapshotEncoder = null;', $code);
        $this->assertStringNotContainsString('private mixed $snapshotDecoder = null;', $code);
        $this->assertStringNotContainsString('$this->snapshotEncoder = $snapshotEncoder === null ? null : \Closure::fromCallable($snapshotEncoder);', $code);
        $this->assertStringNotContainsString('$this->snapshotDecoder = $snapshotDecoder === null ? null : \Closure::fromCallable($snapshotDecoder);', $code);
        $this->assertStringContainsString('private \Closure $arrayLikeChecker;', $code);
        $this->assertStringContainsString('?callable $arrayLikeChecker = null', $code);
        $this->assertStringContainsString('private function isArrayLike(mixed $value): bool', $code);
        $this->assertStringContainsString('$this->isArrayLike($keys)', $code);
        $this->assertStringNotContainsString('is_array_alt(', $code);
        $this->assertStringContainsString('private \Closure $namespaceResolver;', $code);
        $this->assertStringContainsString('?callable $namespaceResolver = null', $code);
        $this->assertStringContainsString('$this->namespaceResolver = \Closure::fromCallable(', $code);
        $this->assertStringContainsString('static fn(object|string $object, int $depth = 1): string => self::nativeNamespaceName($object, $depth)', $code);
        $this->assertStringContainsString('protected function namespaceName(object|string $object, int $depth = 1): string', $code);
        $this->assertStringNotContainsString('private ?\Closure $namespaceResolver', $code);
        $this->assertStringNotContainsString('$this->namespaceResolver === null', $code);
        $this->assertStringNotContainsString('get_ns_name(', $code);
    }

}

final class BaseModelRowFieldKeyList implements IteratorAggregate
{
    public function __construct(private array $keys)
    {
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->keys);
    }
}

final class BaseModelRowNamespaceProbe extends row
{
    public function exposeNamespaceName(object|string $object, int $depth = 1): string
    {
        return $this->namespaceName($object, $depth);
    }
}

final class BaseModelRowEntityDouble extends entity
{
    private BaseModelRowDescriptionDouble $descriptionDouble;

    public function __construct()
    {
        $this->tableName = 'users';
        $this->name = 'users';
        $this->descriptionDouble = new BaseModelRowDescriptionDouble();
    }

    public function getDescription(array $param = []): object
    {
        return $this->descriptionDouble;
    }

    public function getConfig(mixed $key = null, mixed $default = null): mixed
    {
        return $default;
    }

    public function getMainParam(): array
    {
        return [
            'collection' => 0,
            'name' => 'users',
            'class' => self::class,
            'param' => [],
            'connection' => [
                'name' => 'default',
                'key' => 0,
            ],
        ];
    }

    public function setConnectionName(?string $name = null): static
    {
        return $this;
    }

    public function setConnectionKey(mixed $key): static
    {
        return $this;
    }
}

final class BaseModelRowDescriptionDouble
{
    public function __construct()
    {
    }

    public function getPrimeryKey(): string
    {
        return 'id';
    }

    public function getFields(mixed $force = false): array
    {
        return [
            'id' => ['type' => 'int', 'default' => null, 'null' => false, 'auto_increment' => true],
            'name' => ['type' => 'varchar', 'length' => 3, 'charset' => 'utf8', 'default' => '', 'null' => false, 'auto_increment' => false],
            'age' => ['type' => 'int', 'default' => 18, 'null' => false, 'auto_increment' => false],
            'title_en' => ['type' => 'varchar', 'length' => 20, 'charset' => 'utf8', 'default' => '', 'null' => false, 'auto_increment' => false],
            'title_de' => ['type' => 'varchar', 'length' => 20, 'charset' => 'utf8', 'default' => '', 'null' => false, 'auto_increment' => false],
        ];
    }
}

final class BaseModelRowLocaleDouble
{
    public function __construct(private string $language, private string $defaultLanguage)
    {
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getDefaultLanguage(): string
    {
        return $this->defaultLanguage;
    }
}

final class BaseModelRowErrorDouble
{
    public array $messages = [];

    public function logErrorMessage(
        string $message,
        string $title,
        string $note = '',
        bool $isTrace = true,
        bool $displayError = true
    ): void {
        $this->messages[] = [$message, $title, $note, $isTrace, $displayError];
    }
}

final class BaseModelRowEntityServiceDouble
{
    public function get(string $name, array $param = []): BaseModelRowEntityDouble
    {
        return new BaseModelRowEntityDouble();
    }

    public function getAnonymous(string $class, array $param = []): BaseModelRowEntityDouble
    {
        return new BaseModelRowEntityDouble();
    }
}
