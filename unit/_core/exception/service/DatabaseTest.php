<?php

declare(strict_types=1);

use FanTest\_core\block\FakeServiceRegistry;
use FanTest\_core\exception\FakeErrorService;
use FanTest\_core\exception\FakeRequestService;
use FanTest\_core\exception\TestDatabaseService;

require_once __DIR__ . '/../../../mock/_core/exception/ExceptionDoubles.php';
require_once __DIR__ . '/../../../../_core/exception/base.php';
require_once __DIR__ . '/../../../../_core/exception/service/fatal.php';
require_once __DIR__ . '/../../../../_core/exception/service/database.php';

class ExceptionServiceDatabaseTest extends \PHPUnit\Framework\TestCase
{
    private FakeErrorService $errorService;

    protected function setUp(): void
    {
        FakeServiceRegistry::reset();
        \fan\project\service\database::reset();
        if (method_exists(\fan\project\service\error::class, 'reset')) {
            \fan\project\service\error::reset();
        }

        $this->errorService = new FakeErrorService();
        FakeServiceRegistry::set('request', new FakeRequestService());
        FakeServiceRegistry::set('error', $this->errorService);
    }

    public function testDatabaseExceptionExposesOperationErrorAndSql(): void
    {
        $database = new TestDatabaseService('reporting', 'nothing');

        $exception = new \fan\core\exception\service\database(
            $database,
            2,
            'SELECT failed',
            1064,
            'Syntax error',
            'SELECT * FROM broken'
        );

        $this->assertSame($database, $exception->getService());
        $this->assertSame(2, $exception->getOperationCode());
        $this->assertSame('SELECT failed', $exception->getOperation());
        $this->assertSame(1064, $exception->getErrorNum());
        $this->assertSame('Syntax error', $exception->getMessageForShow());
        $this->assertSame('SELECT * FROM broken', $exception->getParsedSql());
        $this->assertStringContainsString('SELECT failed', $exception->getMessageForLog());
        $this->assertStringContainsString('Error No: 1064.', $exception->getMessageForLog());
    }

    public function testLowLevelOperationUsesGenericServiceFatalLogging(): void
    {
        $database = new TestDatabaseService('main', 'service');

        new \fan\core\exception\service\database($database, 1, 'CONNECT', 2002, 'No route', 'CONNECT SQL');

        $this->assertCount(1, $this->errorService->exceptionMessages);
        $this->assertStringContainsString('Service fatal error (' . TestDatabaseService::class . '). CONNECT', $this->errorService->exceptionMessages[0][0]);
        $this->assertSame([], \fan\project\service\error::instance()->databaseErrors);
    }

    public function testQueryOperationIsLoggedAsDatabaseError(): void
    {
        $database = new TestDatabaseService('analytics', 'service');

        new \fan\core\exception\service\database($database, 5, 'UPDATE', 1213, 'Deadlock found', 'UPDATE table SET x = 1');

        $this->assertSame([], $this->errorService->exceptionMessages);
        $this->assertSame(
            [['analytics', 'UPDATE', 'Deadlock found', 1213, 'UPDATE table SET x = 1']],
            $this->errorService->databaseErrors
        );
    }
}
