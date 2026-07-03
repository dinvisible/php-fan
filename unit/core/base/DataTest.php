<?php

declare(strict_types=1);

require_once __DIR__ . '/../../mock/core/base/DataFunctions.php';
require_once __DIR__ . '/../../../core/base/data.php';
require_once __DIR__ . '/../../mock/core/base/TestData.php';

use FanTest\core\base\TestData;
use FanTest\core\base\TestDataSetter;
use FanTest\core\base\TestDataWithoutDefaultSubDataFactory;
use FanTest\core\base\DataErrorLoggerDouble;
use fan\core\base\data;
use fan\project\service\error;
use PHPUnit\Framework\TestCase;


class DataTest extends TestCase
{
    private DataErrorLoggerDouble $logger;

    protected function setUp(): void
    {
        error::reset();
        $this->logger = new DataErrorLoggerDouble();
    }

    public function testConstructorGetAndToArraySupportNestedData(): void
    {
        $data = new TestData([
            'name' => 'fan',
            'empty' => '',
            'zero' => 0,
            'nested' => ['level' => ['value' => 5]],
        ]);

        $this->assertSame('fan', $data->get('name'));
        $this->assertSame('', $data->get('empty'));
        $this->assertSame(0, $data->get('zero'));
        $this->assertSame(5, $data->get(['nested', 'level', 'value']));
        $this->assertSame('fallback', $data->get('missing', 'fallback'));
        $this->assertSame($data, $data->get());
        $this->assertSame([
            'name' => 'fan',
            'empty' => '',
            'zero' => 0,
            'nested' => ['level' => ['value' => 5]],
        ], $data->toArray());
    }

    public function testSetSupportsScalarNestedAndNoRewriteBranches(): void
    {
        $data = new TestData(['name' => 'old'], null, null, $this->logger);

        $this->assertSame($data, $data->set('name', 'new'));
        $this->assertSame('new', $data->name);

        $this->assertSame($data, $data->set(['nested', 'key'], 'value'));
        $this->assertSame('value', $data->get(['nested', 'key']));

        $this->assertSame($data, $data->set('name', 'blocked', false));
        $this->assertSame('new', $data->get('name'));
        $this->assertCount(1, $this->logger->messages);
        $this->assertStringContainsString('inpossible', $this->logger->messages[0][0]);
    }

    public function testArrayAccessIteratorCountMagicUnsetAndStringConversion(): void
    {
        $data = new TestData(['a' => 1, 'b' => false, 'c' => null]);

        $this->assertTrue(isset($data['a']));
        $this->assertSame(1, $data['a']);
        $data['d'] = 'text';
        $this->assertSame('text', $data->d);
        unset($data['a']);
        $this->assertFalse(isset($data['a']));
        unset($data->b);
        $this->assertFalse(isset($data->b));
        $this->assertSame(2, count($data));

        $seen = [];
        foreach ($data as $key => $value) {
            $seen[$key] = $value;
        }
        $this->assertSame(['c' => null, 'd' => 'text'], $seen);
        $this->assertStringContainsString('c => (NULL)', (string)$data);
        $this->assertStringContainsString('d => (string) text', (string)$data);
    }

    public function testSetterRestrictionsAllowOnlyRegisteredSetter(): void
    {
        $data = new TestData(['locked' => 'initial'], null, null, $this->logger);
        $setter = new TestDataSetter();
        $this->assertTrue($data->allowSetter($setter));

        $data->set('locked', 'blocked');
        $this->assertSame('initial', $data->get('locked'));
        $this->assertCount(1, $this->logger->messages);

        $this->assertSame($data, $setter->setValue($data, 'locked', 'allowed'));
        $this->assertSame('allowed', $data->get('locked'));
        $setter->unsetValue($data, 'locked');
        $this->assertFalse(isset($data->locked));
    }

    public function testInvalidKeyTypeLogsAndNonMultilevelArrayPathUsesArrayHelper(): void
    {
        $data = new TestData([], null, null, $this->logger);

        $this->assertSame('fallback', $data->get(new \stdClass(), 'fallback', true));
        $this->assertCount(1, $this->logger->messages);
        $this->assertStringContainsString('Incorrect type of key', $this->logger->messages[0][0]);

        $data->setMultiLevel(false);
        $data->set(['flat', 'path'], 'stored');
        $this->assertSame(['flat' => ['path' => 'stored']], $data->toArray());
    }

    public function testInjectedErrorLoggerReceivesRootAndNestedErrors(): void
    {
        $logger = new DataErrorLoggerDouble();
        $data = new TestData(['nested' => ['value' => 'one']], null, null, $logger);

        $this->assertSame('fallback', $data->get(new \stdClass(), 'fallback', true));
        $data->get('nested')->set('value', 'two', false);

        $this->assertCount(2, $logger->messages);
        $this->assertStringContainsString('Incorrect type of key', $logger->messages[0][0]);
        $this->assertStringContainsString('inpossible', $logger->messages[1][0]);
        $this->assertCount(0, error::instance()->messages);
    }

    public function testSubDataFactoryCanBeInjectedAndInheritedByNestedData(): void
    {
        $createdKeys = [];
        $factory = function (mixed $value, int|string|null $key, data $superior) use (&$createdKeys): TestData {
            $createdKeys[] = $key;

            return new TestData($value, $key, $superior);
        };

        $data = new TestData([], null, null, $this->logger, $factory);
        $data->set(['first', 'second', 'value'], 'stored');

        $this->assertSame(['first', 'second'], $createdKeys);
        $this->assertSame('stored', $data->get(['first', 'second', 'value']));
        $this->assertSame($this->logger, $data->get('first')->get('second')->exposeErrorLogger());
    }

    public function testMissingSubDataFactoryFailsAtInjectedBoundary(): void
    {
        $data = new TestDataWithoutDefaultSubDataFactory([], null, null, $this->logger);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Sub-data factory is not configured for data class "FanTest\core\base\TestDataWithoutDefaultSubDataFactory".');

        $data->set(['nested', 'value'], 'stored');
    }

    public function testClassNameResolverIsInjectedAndInheritedByNestedData(): void
    {
        $resolvedObjects = [];
        $classNameResolver = static function (object $object) use (&$resolvedObjects): string {
            $resolvedObjects[] = get_class($object);

            return get_class($object);
        };

        $data = new TestData([], null, null, $this->logger, null, null, null, $classNameResolver);
        $data->set(['first', 'second', 'value'], 'stored');
        $data->toArray();

        $this->assertContains(TestData::class, $resolvedObjects);
        $this->assertSame('stored', $data->get(['first', 'second', 'value']));
    }

    public function testSerializationRestoresNestedParentPointersAndData(): void
    {
        $data = new TestData(['nested' => ['x' => 1]]);

        $restored = unserialize(serialize($data));

        $this->assertInstanceOf('FanTest\core\base\TestData', $restored);
        $this->assertSame(['nested' => ['x' => 1]], $restored->toArray());
        $subData = $restored->exposeSubData();
        $this->assertCount(1, $subData);
        $this->assertInstanceOf('FanTest\core\base\TestData', $subData[0]);
    }

    public function testSerializedDataRestoresDefaultRuntimeResolvers(): void
    {
        $data = new TestData(['nested' => ['x' => 1]]);
        $restored = unserialize(serialize($data));

        $this->assertSame(['nested' => ['x' => 1]], $restored->toArray());
    }

    public function testManualSnapshotSerializationUsesInjectedCodec(): void
    {
        $encodedState = null;
        $calls = [];
        $encoder = static function (mixed $state) use (&$encodedState, &$calls): string {
            $encodedState = $state;
            $calls[] = ['encode', $state['data']];

            return 'snapshot';
        };
        $decoder = static function (string $payload, mixed $default = null) use (&$encodedState, &$calls): mixed {
            $calls[] = ['decode', $payload, $default];

            return $encodedState;
        };

        $data = new TestData(['nested' => ['x' => 1]], null, null, $this->logger, null, $encoder, $decoder);
        $this->assertSame('snapshot', $data->serialize());

        $restored = new TestData(null, null, null, $this->logger, null, $encoder, $decoder);
        $restored->unserialize('snapshot');

        $this->assertSame(['nested' => ['x' => 1]], $restored->toArray());
        $this->assertSame([
            ['encode', ['nested' => $data->get('nested')]],
            ['decode', 'snapshot', []],
        ], $calls);
    }

    public function testSourceNoLongerFallsBackToProjectErrorSingleton(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/base/data.php');

        $this->assertIsString($source);
        $this->assertStringNotContainsString('::instance()', $source);
        $this->assertStringNotContainsString('safe_serializer::', $source);
        $this->assertStringNotContainsString('get_class_alt(', $source);
        $this->assertStringContainsString('private ?\Closure $subDataFactory = null;', $source);
        $this->assertStringContainsString('private ?\Closure $classNameResolver = null;', $source);
        $this->assertStringContainsString('?callable $subDataFactory = null', $source);
        $this->assertStringContainsString('?callable $classNameResolver = null', $source);
        $this->assertStringContainsString('private function subDataFactory(): callable', $source);
        $this->assertStringContainsString('private function className(object $object): string', $source);
        $this->assertStringContainsString('private function defaultSubDataFactory(): \Closure', $source);
        $this->assertStringContainsString('private function defaultClassNameResolver(): \Closure', $source);
        $this->assertStringNotContainsString('private mixed $subDataFactory', $source);
        $this->assertStringNotContainsString('private mixed $classNameResolver', $source);
        $this->assertStringContainsString('Sub-data factory is not configured for data class', $source);
        $this->assertStringNotContainsString('new static(', $source);
        $this->assertStringNotContainsString('return $this->errorLogger = new class', $source);
        $this->assertStringContainsString('Data error logger dependency is not configured.', $source);
    }
}
