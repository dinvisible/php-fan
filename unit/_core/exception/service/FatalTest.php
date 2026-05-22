<?php

declare(strict_types=1);

use FanTest\_core\block\FakeServiceRegistry;
use FanTest\_core\exception\FakeErrorService;
use FanTest\_core\exception\FakeRequestService;
use FanTest\_core\exception\TestService;

require_once __DIR__ . '/../../../mock/_core/exception/ExceptionDoubles.php';
require_once __DIR__ . '/../../../../_core/exception/base.php';
require_once __DIR__ . '/../../../../_core/exception/service/fatal.php';

class ExceptionServiceFatalTest extends \PHPUnit\Framework\TestCase
{
    private FakeErrorService $errorService;

    protected function setUp(): void
    {
        FakeServiceRegistry::reset();
        \bootstrap::$log = [];
        \fan\project\service\database::reset();

        $this->errorService = new FakeErrorService();
        FakeServiceRegistry::set('request', new FakeRequestService());
        FakeServiceRegistry::set('error', $this->errorService);
    }

    public function testServiceFatalKeepsServiceRollsBackAndLogsToErrorService(): void
    {
        $service = new TestService('service', 'rollback');

        $exception = new \fan\core\exception\service\fatal($service, 'Service failed', E_USER_ERROR);

        $this->assertSame($service, $exception->getService());
        $this->assertSame('Service failed', $exception->getMessage());
        $this->assertSame(E_USER_ERROR, $exception->getCode());
        $this->assertSame('rollback', $exception->getDbOper());
        $this->assertSame([['rollback', true]], \fan\project\service\database::$calls);
        $this->assertCount(1, $this->errorService->exceptionMessages);
        $this->assertStringContainsString('Service fatal error (' . TestService::class . '). Service failed', $this->errorService->exceptionMessages[0][0]);
        $this->assertSame('Log exception', $this->errorService->exceptionMessages[0][1]);
        $this->assertSame('GET /unit-test', $this->errorService->exceptionMessages[0][2]);
    }

    public function testServiceFatalCanLogToPhpOrStaySilent(): void
    {
        new \fan\core\exception\service\fatal(new TestService('php'), 'PHP log branch', E_USER_WARNING);
        $this->assertCount(1, \bootstrap::$log);
        $this->assertStringContainsString('PHP log branch', \bootstrap::$log[0]);
        $this->assertSame([], $this->errorService->exceptionMessages);

        new \fan\core\exception\service\fatal(new TestService('nothing'), 'Silent branch', E_USER_WARNING);
        $this->assertCount(1, \bootstrap::$log);
        $this->assertSame([], $this->errorService->exceptionMessages);
    }
}
