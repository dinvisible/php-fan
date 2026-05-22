<?php

declare(strict_types=1);

use FanTest\_core\base\meta\TestMetaMaker;

require_once __DIR__ . '/../../../mock/_core/base/meta/MetaDoubles.php';

class MetaRowTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        \fan\project\service\error::reset();
    }

    public function testConstructorConvertsNestedArraysToProjectMetaRows(): void
    {
        $maker = new TestMetaMaker();
        $row = new \fan\project\base\meta\row($maker, [
            'plain' => 'value',
            'nested' => [
                'leaf' => 7,
                'deep' => ['name' => 'fan'],
            ],
        ]);

        $this->assertSame('value', $row->get('plain'));
        $this->assertInstanceOf(\fan\project\base\meta\row::class, $row->get('nested'));
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
        $row = new \fan\project\base\meta\row($maker, [
            'title' => 'old',
            'nested' => ['keep' => 'yes'],
        ]);

        $this->assertSame($row, $maker->mergeRow($row, [
            'title' => 'new',
            'nested' => ['added' => 'ok'],
        ]));
        $this->assertSame('new', $row->get('title'));
        $this->assertSame(['keep' => 'yes', 'added' => 'ok'], $row->get('nested')->toArray());

        $maker->mergeRow($row, ['title' => 'blocked'], false);

        $this->assertSame('new', $row->get('title'));
        $this->assertCount(1, \fan\project\service\error::instance()->messages);
        $this->assertStringContainsString('Set new data data inpossible', \fan\project\service\error::instance()->messages[0][0]);
    }

    public function testExternalMutationIsRejectedButMakerCanSetPathValues(): void
    {
        $maker = new TestMetaMaker();
        $row = new \fan\project\base\meta\row($maker, ['safe' => 'initial']);

        $row->set('safe', 'external');

        $this->assertSame('initial', $row->get('safe'));
        $this->assertCount(1, \fan\project\service\error::instance()->messages);
        $this->assertStringContainsString('Unrecognised setter', \fan\project\service\error::instance()->messages[0][0]);

        $maker->mergeRow($row, ['nested' => ['key' => 'allowed']]);

        $this->assertSame('allowed', $row->get(['nested', 'key']));
    }
}
