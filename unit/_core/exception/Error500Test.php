<?php

declare(strict_types=1);

use FanTest\_core\block\FakeServiceRegistry;
use FanTest\_core\exception\FakeErrorService;
use FanTest\_core\exception\FakeRequestService;

require_once __DIR__ . '/../../mock/_core/exception/ExceptionDoubles.php';
require_once __DIR__ . '/../../../_core/exception/base.php';
require_once __DIR__ . '/../../../_core/exception/error500.php';

class ExceptionError500Test extends \PHPUnit\Framework\TestCase
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

    public function testError500KeepsInternalMessageAndLogsThroughErrorService(): void
    {
        $exception = new \fan\core\exception\error500('controller exploded', E_USER_WARNING);

        $this->assertSame('controller exploded', $exception->getMessage());
        $this->assertSame(E_USER_WARNING, $exception->getCode());
        $this->assertSame('controller exploded', $exception->getMessageForLog());
        $this->assertSame('Please visit the site later.', $exception->getMessageForShow());
        $this->assertSame('error_500', $exception->getErrorFile());
        $this->assertNull($exception->getDbOper());
        $this->assertSame([], \fan\project\service\database::$calls);
        $this->assertSame(
            [['controller exploded', 'Error 500', 'GET /unit-test']],
            $this->errorService->exceptionMessages
        );
    }
}
