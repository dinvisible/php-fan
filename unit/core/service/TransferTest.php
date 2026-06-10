<?php

declare(strict_types=1);

use fan\core\service\transfer;
use FanTest\core\SourceFileContractTestCase;
use FanTest\core\base\DatabaseConnectionsStub;
use fan\core\base\transfer as base_transfer;
use fan\core\base\transfer\out;
use fan\core\base\transfer\sham;
use fan\core\base\transfer\transfer_int;
use fan\core\di\transfer_exception_factory;


require_once dirname(__DIR__, 3) . '/core/factory/transfer_exception_factory.php';
require_once dirname(__DIR__, 2) . '/mock/core/base/TransferStubs.php';
require_once dirname(__DIR__, 3) . '/core/base/transfer.php';
require_once dirname(__DIR__, 3) . '/core/base/transfer/out.php';
require_once dirname(__DIR__, 3) . '/core/base/transfer/transfer_int.php';
require_once dirname(__DIR__, 3) . '/core/base/transfer/sham.php';

final class ServiceTransferTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/service/transfer.php';

    protected function setUp(): void
    {
        DatabaseConnectionsStub::reset();
    }

    public function testOutThrowsTransferWithInjectedDatabaseConnections(): void
    {
        $service = new transfer(
            new DatabaseConnectionsStub(),
            new transfer_exception_factory()
        );

        try {
            $service->out('/next', 'a=1');
            $this->fail('Expected transfer exception.');
        } catch (out $transfer) {
            $this->assertSame('out', $transfer->getTransferType());
            $this->assertSame('/next?a=1', $transfer->getRequest());
        }

        $this->assertSame([['commit', false]], DatabaseConnectionsStub::$calls);
    }

    public function testInternalAndShamTransfersKeepTheirTypes(): void
    {
        $service = new transfer(
            new DatabaseConnectionsStub(),
            new transfer_exception_factory()
        );

        try {
            $service->int('/internal');
            $this->fail('Expected internal transfer.');
        } catch (transfer_int $transfer) {
            $this->assertSame('int', $transfer->getTransferType());
        }

        try {
            $service->sham('/same');
            $this->fail('Expected sham transfer.');
        } catch (sham $transfer) {
            $this->assertSame('sham', $transfer->getTransferType());
            $this->assertFalse($transfer->isShiftCurrent());
        }
    }

    public function testTransferExceptionFactoryIsInjected(): void
    {
        $databaseConnections = new DatabaseConnectionsStub();
        $calls = [];
        $service = new transfer(
            $databaseConnections,
            static function (
                string $type,
                string $newUrl,
                ?string $newQueryString,
                ?string $dbOper,
                object $injectedDatabaseConnections
            ) use (&$calls): base_transfer {
                $calls[] = [$type, $newUrl, $newQueryString, $dbOper, $injectedDatabaseConnections];

                return new sham($newUrl, $newQueryString);
            }
        );

        try {
            $service->out('/factory', 'x=1', 'rollback');
            $this->fail('Expected injected transfer exception.');
        } catch (sham $transfer) {
            $this->assertSame('/factory?x=1', $transfer->getRequest());
        }

        $this->assertCount(1, $calls);
        $this->assertSame('out', $calls[0][0]);
        $this->assertSame('/factory', $calls[0][1]);
        $this->assertSame('x=1', $calls[0][2]);
        $this->assertSame('rollback', $calls[0][3]);
        $this->assertSame($databaseConnections, $calls[0][4]);
    }

    public function testSourceDoesNotOwnConcreteTransferConstruction(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/service/transfer.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('private $transferExceptionFactory;', $source);
        $this->assertStringContainsString('throw $this->createTransfer(', $source);
        $this->assertStringNotContainsString('new \fan\project\base\transfer\out(', $source);
        $this->assertStringNotContainsString('new \fan\project\base\transfer\transfer_int(', $source);
        $this->assertStringNotContainsString('new \fan\project\base\transfer\sham(', $source);
    }
}
