<?php

declare(strict_types=1);

use FanTest\_core\block\FakeServiceRegistry;
use FanTest\_core\exception\FakeErrorService;
use FanTest\_core\exception\FakeRequestService;

require_once __DIR__ . '/../../../mock/_core/exception/ExceptionDoubles.php';
require_once __DIR__ . '/../../../../_core/exception/base.php';
require_once __DIR__ . '/../../../../_core/exception/template/fatal.php';

class ExceptionTemplateFatalTest extends \PHPUnit\Framework\TestCase
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

    public function testTemplateFatalLogsTemplateClassAndUsesDefaultPublicError(): void
    {
        $template = new class {
        };

        $exception = new \fan\core\exception\template\fatal($template, 'Template parse failed', E_USER_ERROR);

        $this->assertSame('Template parse failed', $exception->getMessage());
        $this->assertSame('Please visit the site later.', $exception->getMessageForShow());
        $this->assertSame('error_500', $exception->getErrorFile());
        $this->assertNull($exception->getDbOper());
        $this->assertSame(
            [['Template parse failed', 'Template\'s exception (' . get_class($template) . ').', 'GET /unit-test']],
            $this->errorService->exceptionMessages
        );
    }
}
