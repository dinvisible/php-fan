<?php

declare(strict_types=1);

use FanTest\core\block\FakeServiceRegistry;
use FanTest\core\exception\FakeErrorService;
use FanTest\core\exception\FakeRequestService;
use FanTest\core\exception\TestService;
use fan\core\exception\service\fatal;
use PHPUnit\Framework\TestCase;


require_once __DIR__ . '/../../../mock/core/exception/ExceptionDoubles.php';
require_once __DIR__ . '/../../../../core/exception/base.php';
require_once __DIR__ . '/../../../../core/exception/service/fatal.php';

class ExceptionServiceFatalTest extends TestCase
{
    private FakeErrorService $errorService;

    protected function setUp(): void
    {
        FakeServiceRegistry::reset();
        \bootstrap::$log = [];

        $this->errorService = new FakeErrorService();
        FakeServiceRegistry::set('request', new FakeRequestService());
        FakeServiceRegistry::set('error', $this->errorService);
    }

    public function testServiceFatalKeepsServiceRollsBackAndLogsToErrorService(): void
    {
        $service = new TestService('service', 'rollback');

        $exception = new fatal(
            $service,
            'Service failed',
            E_USER_ERROR,
            exceptionDatabaseConnections: FakeServiceRegistry::get('database_connections'),
            exceptionRequestService: FakeServiceRegistry::get('request'),
            exceptionErrorService: $this->errorService
        );

        $this->assertSame($service, $exception->getService());
        $this->assertSame('Service failed', $exception->getMessage());
        $this->assertSame(E_USER_ERROR, $exception->getCode());
        $this->assertSame('rollback', $exception->getDbOper());
        $this->assertSame([['rollback', true]], FakeServiceRegistry::get('database_connections')->calls);
        $this->assertCount(1, $this->errorService->exceptionMessages);
        $this->assertStringContainsString('Service fatal error (' . TestService::class . '). Service failed', $this->errorService->exceptionMessages[0][0]);
        $this->assertSame('Log exception', $this->errorService->exceptionMessages[0][1]);
        $this->assertSame('GET /unit-test', $this->errorService->exceptionMessages[0][2]);
    }

    public function testServiceFatalCanLogToPhpOrStaySilent(): void
    {
        new fatal(
            new TestService('php'),
            'PHP log branch',
            E_USER_WARNING,
            exceptionRuntimeLogger: FakeServiceRegistry::get('bootstrap_runtime'),
            exceptionRequestService: FakeServiceRegistry::get('request'),
            exceptionErrorService: $this->errorService
        );
        $this->assertCount(1, \bootstrap::$log);
        $this->assertStringContainsString('PHP log branch', \bootstrap::$log[0]);
        $this->assertSame([], $this->errorService->exceptionMessages);

        new fatal(
            new TestService('nothing'),
            'Silent branch',
            E_USER_WARNING,
            exceptionRuntimeLogger: FakeServiceRegistry::get('bootstrap_runtime'),
            exceptionRequestService: FakeServiceRegistry::get('request'),
            exceptionErrorService: $this->errorService
        );
        $this->assertCount(1, \bootstrap::$log);
        $this->assertSame([], $this->errorService->exceptionMessages);
    }

    public function testServiceFatalDoesNotCascadeWhenServiceLoggerDependenciesAreMissing(): void
    {
        $exception = new fatal(
            new TestService('service'),
            'Early bootstrap failure'
        );

        $this->assertSame('Early bootstrap failure', $exception->getMessage());
        $this->assertSame([], $this->errorService->exceptionMessages);
    }
}
