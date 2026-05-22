<?php

declare(strict_types=1);

use FanTest\_core\block\FakeServiceRegistry;
use FanTest\_core\exception\FakeErrorService;
use FanTest\_core\exception\FakeRequestService;
use FanTest\_core\exception\TestEntity;

require_once __DIR__ . '/../../../../mock/_core/exception/ExceptionDoubles.php';
require_once __DIR__ . '/../../../../../_core/exception/base.php';
require_once __DIR__ . '/../../../../../_core/exception/model/entity/fatal.php';

class ExceptionModelEntityFatalTest extends \PHPUnit\Framework\TestCase
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

    public function testEntityFatalKeepsEntityRollsBackAndLogsEntitySnapshot(): void
    {
        $entity = new TestEntity();

        $exception = new \fan\core\exception\model\entity\fatal($entity, 'Entity save failed', E_USER_ERROR);

        $this->assertSame($entity, $exception->getEntity());
        $this->assertSame('Entity save failed', $exception->getMessage());
        $this->assertSame('rollback', $exception->getDbOper());
        $this->assertSame([['rollback', true]], \fan\project\service\database::$calls);
        $this->assertSame(
            [['Entity save failed', 'Entity fatal error (' . TestEntity::class . ').', 'entity snapshot']],
            $this->errorService->exceptionMessages
        );
    }
}
