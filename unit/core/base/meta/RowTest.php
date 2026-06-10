<?php

declare(strict_types=1);

use FanTest\core\base\meta\TestMetaMaker;
use fan\core\base\meta\maker;
use fan\core\base\meta\row;
use fan\project\base\meta\row as meta_row;
use fan\project\service\error;
use PHPUnit\Framework\TestCase;


require_once __DIR__ . '/../../../mock/core/base/meta/MetaDoubles.php';

class MetaRowTest extends TestCase
{
    private object $errorLogger;

    protected function setUp(): void
    {
        error::reset();
        $this->errorLogger = error::instance();
    }

    public function testConstructorConvertsNestedArraysToProjectMetaRows(): void
    {
        $createdKeys = [];
        $rowFactory = function (
            maker $maker,
            array $data,
            ?row $parent = null,
            int|string|null $keyName = null,
            ?callable $rowFactory = null
        ) use (&$createdKeys): object {
            $createdKeys[] = $keyName;

            return new meta_row($maker, $data, $parent, $keyName, $rowFactory);
        };
        $maker = new TestMetaMaker();
        $row = new meta_row($maker, [
            'plain' => 'value',
            'nested' => [
                'leaf' => 7,
                'deep' => ['name' => 'fan'],
            ],
        ], null, null, $rowFactory);

        $this->assertSame(['nested', 'deep'], $createdKeys);
        $this->assertSame('value', $row->get('plain'));
        $this->assertInstanceOf(meta_row::class, $row->get('nested'));
        $this->assertSame(7, $row->get(['nested', 'leaf']));
        $this->assertSame('fan', $row->get(['nested', 'deep', 'name']));
        $this->assertSame([
            'plain' => 'value',
            'nested' => [
                'leaf' => 7,
                'deep' => ['name' => 'fan'],
            ],
        ], $row->toArray());
    }

    public function testMergeDataIsAllowedThroughMakerAndHonorsRewriteFlag(): void
    {
        $maker = new TestMetaMaker();
        $row = new meta_row($maker, [
            'title' => 'old',
            'nested' => ['keep' => 'yes'],
        ]);
        $row->setErrorLogger($this->errorLogger);

        $this->assertSame($row, $maker->mergeRow($row, [
            'title' => 'new',
            'nested' => ['added' => 'ok'],
        ]));
        $this->assertSame('new', $row->get('title'));
        $this->assertSame(['keep' => 'yes', 'added' => 'ok'], $row->get('nested')->toArray());

        $maker->mergeRow($row, ['title' => 'blocked'], false);

        $this->assertSame('new', $row->get('title'));
        $this->assertCount(1, $this->errorLogger->messages);
        $this->assertStringContainsString('Set new data data inpossible', $this->errorLogger->messages[0][0]);
    }

    public function testExternalMutationIsRejectedButMakerCanSetPathValues(): void
    {
        $maker = new TestMetaMaker();
        $row = new meta_row($maker, ['safe' => 'initial']);
        $row->setErrorLogger($this->errorLogger);

        $row->set('safe', 'external');

        $this->assertSame('initial', $row->get('safe'));
        $this->assertCount(1, $this->errorLogger->messages);
        $this->assertStringContainsString('Unrecognised setter', $this->errorLogger->messages[0][0]);

        $maker->mergeRow($row, ['nested' => ['key' => 'allowed']]);

        $this->assertSame('allowed', $row->get(['nested', 'key']));
    }

    public function testSourceUsesInjectedRowFactoryForNestedRows(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/core/base/meta/row.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('private $rowFactory = null;', $source);
        $this->assertStringContainsString('$this->rowFactory = $rowFactory ?? $maker->getRowFactory();', $source);
        $this->assertStringContainsString('$ret[$k] = is_array($v) ? $this->createRow($v, $this, $k) : $v;', $source);
        $this->assertStringContainsString('return $this->createRow($value, $this, $key);', $source);
        $this->assertStringNotContainsString('new \fan\project\base\meta\row', $source);
        $this->assertStringNotContainsString('new $class($this->maker', $source);
    }
}
