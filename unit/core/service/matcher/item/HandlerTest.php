<?php

declare(strict_types=1);

use fan\core\service\matcher\item;
use fan\core\service\matcher\item\handler;
use FanTest\core\SourceFileContractTestCase;

class ServiceMatcherItemHandlerTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/service/matcher/item/handler.php';

    public function testHandlerStoresRoutingDecisionParts(): void
    {
        $handler = new handler($this->item());

        $handler['key'] = 'admin';
        $handler['method'] = 'get';
        $handler['param'] = ['id' => 10];
        $handler['ctrlKey'] = 'admin_rule';
        $handler['reqKey'] = 2;

        $this->assertSame('admin', $handler->key);
        $this->assertSame('get', $handler['method']);
        $this->assertSame(['id' => 10], $handler->param);
        $this->assertSame('admin_rule', $handler['ctrlKey']);
        $this->assertSame(2, $handler['reqKey']);
    }

    public function testHandlerExportsAllKnownParts(): void
    {
        $handler = new handler($this->item());
        $handler->key = 'default';
        $handler->method = 'post';

        $this->assertSame([
            'key' => 'default',
            'service' => null,
            'method' => 'post',
            'param' => null,
            'ctrlKey' => null,
            'reqKey' => null,
        ], $handler->toArray());
    }

    public function testHandlerPartsAreImmutableAfterTheyAreSet(): void
    {
        $handler = new handler($this->item());
        $handler->method = 'get';

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Error. Try to change existing property.');

        $handler->method = 'post';
    }

    private function item(): item
    {
        return new class extends item {
            public function __construct()
            {
            }
        };
    }
}
