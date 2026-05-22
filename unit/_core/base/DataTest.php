<?php

declare(strict_types=1);

require_once __DIR__ . '/../../mock/_core/base/DataFunctions.php';
require_once __DIR__ . '/../../../_core/base/data.php';
require_once __DIR__ . '/../../mock/_core/base/TestData.php';

use FanTest\_core\base\TestData;
use FanTest\_core\base\TestDataSetter;

class DataTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        \fan\project\service\error::reset();
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
        $data = new TestData(['name' => 'old']);

        $this->assertSame($data, $data->set('name', 'new'));
        $this->assertSame('new', $data->name);

        $this->assertSame($data, $data->set(['nested', 'key'], 'value'));
        $this->assertSame('value', $data->get(['nested', 'key']));

        $this->assertSame($data, $data->set('name', 'blocked', false));
        $this->assertSame('new', $data->get('name'));
        $this->assertCount(1, \fan\project\service\error::instance()->messages);
        $this->assertStringContainsString('inpossible', \fan\project\service\error::instance()->messages[0][0]);
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
        $data = new TestData(['locked' => 'initial']);
        $setter = new TestDataSetter();
        $this->assertTrue($data->allowSetter($setter));

        $data->set('locked', 'blocked');
        $this->assertSame('initial', $data->get('locked'));
        $this->assertCount(1, \fan\project\service\error::instance()->messages);

        $this->assertSame($data, $setter->setValue($data, 'locked', 'allowed'));
        $this->assertSame('allowed', $data->get('locked'));
        $setter->unsetValue($data, 'locked');
        $this->assertFalse(isset($data->locked));
    }

    public function testInvalidKeyTypeLogsAndNonMultilevelArrayPathUsesArrayHelper(): void
    {
        $data = new TestData([]);

        $this->assertSame('fallback', $data->get(new \stdClass(), 'fallback', true));
        $this->assertCount(1, \fan\project\service\error::instance()->messages);
        $this->assertStringContainsString('Incorrect type of key', \fan\project\service\error::instance()->messages[0][0]);

        $data->setMultiLevel(false);
        $data->set(['flat', 'path'], 'stored');
        $this->assertSame(['flat' => ['path' => 'stored']], $data->toArray());
    }

    public function testSerializationRestoresNestedParentPointersAndData(): void
    {
        $data = new TestData(['nested' => ['x' => 1]]);

        $restored = unserialize(serialize($data));

        $this->assertInstanceOf('FanTest\_core\base\TestData', $restored);
        $this->assertSame(['nested' => ['x' => 1]], $restored->toArray());
        $subData = $restored->exposeSubData();
        $this->assertCount(1, $subData);
        $this->assertInstanceOf('FanTest\_core\base\TestData', $subData[0]);
    }
}
