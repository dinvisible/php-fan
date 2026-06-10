<?php

declare(strict_types=1);

use fan\core\di\transfer_exception_factory;
use PHPUnit\Framework\TestCase;
use FanTest\core\base\DatabaseConnectionsStub;
use fan\core\base\transfer\out;
use fan\core\base\transfer\sham;
use fan\core\base\transfer\transfer_int;


require_once dirname(__DIR__, 2) . '/mock/core/base/TransferStubs.php';
require_once dirname(__DIR__, 3) . '/core/base/transfer.php';
require_once dirname(__DIR__, 3) . '/core/base/transfer/out.php';
require_once dirname(__DIR__, 3) . '/core/base/transfer/transfer_int.php';
require_once dirname(__DIR__, 3) . '/core/base/transfer/sham.php';

final class TransferExceptionFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        DatabaseConnectionsStub::reset();
    }

    public function testFactoryCreatesProjectTransferExceptions(): void
    {
        $factory = new transfer_exception_factory();
        $databaseConnections = new DatabaseConnectionsStub();

        $out = $factory('out', '/next', 'a=1', null, $databaseConnections);
        $this->assertInstanceOf(out::class, $out);
        $this->assertSame('out', $out->getTransferType());
        $this->assertSame('/next?a=1', $out->getRequest());
        $this->assertSame([['commit', false]], DatabaseConnectionsStub::$calls);

        DatabaseConnectionsStub::reset();
        $internal = $factory('int', '/internal', null, 'rollback', $databaseConnections);
        $this->assertInstanceOf(transfer_int::class, $internal);
        $this->assertSame('int', $internal->getTransferType());
        $this->assertSame([['rollback', false]], DatabaseConnectionsStub::$calls);

        DatabaseConnectionsStub::reset();
        $sham = $factory('sham', '/same', null, null, $databaseConnections);
        $this->assertInstanceOf(sham::class, $sham);
        $this->assertSame('sham', $sham->getTransferType());
        $this->assertSame([], DatabaseConnectionsStub::$calls);
    }

    public function testFactoryRejectsUnknownTransferType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown transfer type "missing".');

        (new transfer_exception_factory())(
            'missing',
            '/missing',
            null,
            null,
            new DatabaseConnectionsStub()
        );
    }}
