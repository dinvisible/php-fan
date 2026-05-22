<?php

declare(strict_types=1);

use FanTest\_core\block\FakeServiceRegistry;
use FanTest\_core\exception\FakeErrorService;
use FanTest\_core\exception\FakeRequestService;

require_once __DIR__ . '/../../../mock/_core/exception/ExceptionDoubles.php';
require_once __DIR__ . '/../../../../_core/exception/base.php';
require_once __DIR__ . '/../../../../_core/exception/plain/fatal.php';

class ExceptionPlainFatalTest extends \PHPUnit\Framework\TestCase
{
    private FakeErrorService $errorService;

    protected function setUp(): void
    {
        FakeServiceRegistry::reset();
        \fan\project\service\database::reset();

        $this->errorService = new FakeErrorService();
        FakeServiceRegistry::set('request', new FakeRequestService());
        FakeServiceRegistry::set('error', $this->errorService);
    }

    public function testPlainFatalKeepsControllerAndLogsControllerClass(): void
    {
        $controller = new class {
        };

        $exception = new \fan\core\exception\plain\fatal($controller, 'Plain action failed', E_USER_WARNING);

        $this->assertSame($controller, $exception->getController());
        $this->assertSame('Plain action failed', $exception->getMessage());
        $this->assertSame(E_USER_WARNING, $exception->getCode());
        $this->assertNull($exception->getDbOper());
        $this->assertCount(1, $this->errorService->exceptionMessages);
        $this->assertStringContainsString('Plain controller fatal error (' . get_class($controller) . '). Plain action failed', $this->errorService->exceptionMessages[0][0]);
        $this->assertSame('Log exception', $this->errorService->exceptionMessages[0][1]);
    }
}
