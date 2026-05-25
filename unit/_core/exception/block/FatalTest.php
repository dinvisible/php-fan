<?php

declare(strict_types=1);

use FanTest\_core\block\FakeServiceRegistry;
use FanTest\_core\block\FakeTab;
use FanTest\_core\block\TestableBaseBlock;
use FanTest\_core\exception\FakeErrorService;
use FanTest\_core\exception\FakeRequestService;
use fan\core\exception\block\fatal;
use PHPUnit\Framework\TestCase;


require_once __DIR__ . '/../../../mock/_core/exception/ExceptionDoubles.php';
require_once __DIR__ . '/../../../mock/_core/block/FrameworkStubs.php';
require_once __DIR__ . '/../../../../_core/block/base.php';
require_once __DIR__ . '/../../../mock/_core/block/TestableBaseBlock.php';
require_once __DIR__ . '/../../../../_core/exception/base.php';
require_once __DIR__ . '/../../../../_core/exception/block/local.php';
require_once __DIR__ . '/../../../../_core/exception/block/fatal.php';

class ExceptionBlockFatalTest extends TestCase
{
    private FakeErrorService $errorService;

    protected function setUp(): void
    {
        FakeServiceRegistry::reset();
        TestableBaseBlock::useMeta([]);

        $this->errorService = new FakeErrorService();
        FakeServiceRegistry::set('request', new FakeRequestService());
        FakeServiceRegistry::set('error', $this->errorService);
    }

    public function testBlockFatalKeepsBlockRollsBackAndLogsBlockClass(): void
    {
        $block = new TestableBaseBlock('content', new FakeTab(), null, [], false);
        $headerWriter = new BlockFatalHeaderWriterDouble();

        $exception = new fatal(
            $block,
            'Block render failed',
            E_USER_ERROR,
            exceptionDatabaseConnections: FakeServiceRegistry::get('database_connections'),
            exceptionRequestService: FakeServiceRegistry::get('request'),
            exceptionErrorService: $this->errorService,
            exceptionHeaderWriter: $headerWriter
        );

        $this->assertSame(['HTTP/1.1 500 Internal Server Error'], $headerWriter->headers);
        $this->assertSame($block, $exception->getBlock());
        $this->assertSame('Block render failed', $exception->getMessage());
        $this->assertSame('rollback', $exception->getDbOper());
        $this->assertSame([['rollback', true]], FakeServiceRegistry::get('database_connections')->calls);
        $this->assertSame(
            [['Block render failed', 'Block\'s exception (CLASS: ' . get_class($block) . ').', 'GET /unit-test']],
            $this->errorService->exceptionMessages
        );
    }

    public function testBlockFatalSourceDoesNotCallNativeHeaderDirectly(): void
    {
        $source = file_get_contents(__DIR__ . '/../../../../_core/exception/block/fatal.php');

        $this->assertIsString($source);
        $this->assertStringNotContainsString('headers_sent(', $source);
        $this->assertStringNotContainsString('header(', $source);
        $this->assertStringContainsString('sendInternalServerErrorHeader()', $source);
    }
}

final class BlockFatalHeaderWriterDouble
{
    public array $headers = [];

    public function sent(?string &$file = null, ?int &$line = null): bool
    {
        return false;
    }

    public function send(string $header, bool $replace = true, int $responseCode = 0): void
    {
        $this->headers[] = $header;
    }
}
