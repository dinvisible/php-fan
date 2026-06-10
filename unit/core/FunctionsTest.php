<?php

declare(strict_types=1);

use FanTest\core\SourceFileContractTestCase;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class CoreFunctionsTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/functions.php';

    protected function setUp(): void
    {
        require_once dirname(__DIR__, 2) . '/core/functions.php';
    }

    public function testClassAndNamespaceHelpersHandleObjectsAndStrings(): void
    {
        $object = new class {
        };
        $class = 'fan\\core\\service\\database\\mysql';

        $this->assertSame(get_class($object), get_class_alt($object));
        $this->assertNull(get_class_alt('not-object'));
        $this->assertSame('mysql', get_class_name($class));
        $this->assertSame('fan\\core\\service\\database', get_ns_name($class));
        $this->assertSame('fan\\core\\service', get_ns_name($class, 2));
        $this->assertNull(get_ns_name($class, 41));
    }

    public function testSourceDoesNotOwnLegacyMysqlConstants(): void
    {
        $source = $this->sourceCode();

        $this->assertStringNotContainsString("define('MYSQL_", $source);
        $this->assertStringNotContainsString("defined('MYSQL_", $source);
        $this->assertStringNotContainsString('MYSQL_ASSOC', $source);
        $this->assertStringNotContainsString('MYSQL_NUM', $source);
        $this->assertStringNotContainsString('MYSQL_BOTH', $source);
    }

    public function testArrayHelpersReadMergeAndCreateNestedValues(): void
    {
        $this->assertSame(['a', 'b', null, null], explode_alt(':', 'a:b', 4));
        $this->assertSame([
            'config' => [
                'host' => 'localhost',
                'port' => 3306,
            ],
            'enabled' => true,
        ], array_merge_recursive_alt(
            ['config' => ['host' => 'localhost']],
            ['config' => ['port' => 3306], 'enabled' => true],
        ));

        $source = ['a' => ['b' => 'value']];
        $this->assertSame('value', array_val($source, ['a', 'b']));
        $this->assertSame('fallback', array_val($source, ['a', 'missing'], 'fallback'));

        $slot =& array_get_element($source, ['a', 'created'], true);
        $slot = 'new';

        $this->assertSame('new', $source['a']['created']);
    }

    public function testAdduceToArrayAndNumberScalingHelpers(): void
    {
        $object = new class {
            public function toArray(): array
            {
                return ['from' => 'object'];
            }
        };

        $this->assertSame(['from' => 'object'], adduceToArray($object));
        $this->assertSame(['x'], adduceToArray('x'));
        $this->assertSame([], adduceToArray(null));
        $this->assertSame(1235.0, increaseNum(12.345, 2));
        $this->assertSame(12.35, decreaseNum(1235, 2));
    }
}
