<?php

declare(strict_types=1);

require_once __DIR__ . '/../../mock/_core/base/DataFunctions.php';
require_once __DIR__ . '/../../../_core/base/expression_evaluator.php';
require_once __DIR__ . '/../../../_core/base/service.php';
require_once __DIR__ . '/../../../_core/base/service/single.php';
require_once __DIR__ . '/../../../_core/service/role.php';

class RoleTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        \fan\project\service\error::reset();
    }

    public function testCheckEvaluatesRoleExpressionWithoutEval(): void
    {
        $role = $this->makeRole(['admin', 'editor']);

        $this->assertTrue($role->check('admin'));
        $this->assertTrue($role->check('admin & editor'));
        $this->assertTrue($role->check('admin | missing'));
        $this->assertTrue($role->check('!missing'));
        $this->assertFalse($role->check('admin & missing'));
    }

    public function testCheckReportsInvalidRoleExpression(): void
    {
        $role = $this->makeRole(['admin']);

        $this->assertFalse($role->check('admin && ('));
        $this->assertCount(1, \fan\project\service\error::instance()->messages);
        $this->assertSame('Incorrect role set', \fan\project\service\error::instance()->messages[0][1]);
    }

    private function makeRole(array $roles): \fan\core\service\role
    {
        $reflection = new \ReflectionClass(\fan\core\service\role::class);
        $role = $reflection->newInstanceWithoutConstructor();

        $reflection->getProperty('allRoles')->setValue($role, $roles);
        $reflection->getProperty('fixQttRoles')->setValue($role, []);

        return $role;
    }
}
