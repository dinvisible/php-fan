<?php

declare(strict_types=1);

use FanTest\core\block\FakeServiceRegistry;
use FanTest\core\exception\FakeErrorService;
use FanTest\core\exception\FakeRequestService;
use fan\core\exception\plain\fatal;
use PHPUnit\Framework\TestCase;


require_once __DIR__ . '/../../../mock/core/exception/ExceptionDoubles.php';
require_once __DIR__ . '/../../../../core/exception/base.php';
require_once __DIR__ . '/../../../../core/exception/plain/fatal.php';

class ExceptionPlainFatalTest extends TestCase
{
    private FakeErrorService $errorService;

    protected function setUp(): void
    {
        FakeServiceRegistry::reset();

        $this->errorService = new FakeErrorService();
        FakeServiceRegistry::set('request', new FakeRequestService());
        FakeServiceRegistry::set('error', $this->errorService);
    }

    public function testPlainFatalKeepsControllerAndLogsControllerClass(): void
    {
        $controller = new class {
        };

        $exception = new fatal(
            $controller,
            'Plain action failed',
            E_USER_WARNING,
            exceptionRequestService: FakeServiceRegistry::get('request'),
            exceptionErrorService: $this->errorService
        );

        $this->assertSame($controller, $exception->getController());
        $this->assertSame('Plain action failed', $exception->getMessage());
        $this->assertSame(E_USER_WARNING, $exception->getCode());
        $this->assertNull($exception->getDbOper());
        $this->assertCount(1, $this->errorService->exceptionMessages);
        $this->assertStringContainsString('Plain controller fatal error (' . get_class($controller) . '). Plain action failed', $this->errorService->exceptionMessages[0][0]);
        $this->assertSame('Log exception', $this->errorService->exceptionMessages[0][1]);
    }

    public function testPlainFatalUsesInjectedClassNameResolverForLogContext(): void
    {
        $controller = new class {
        };

        new fatal(
            $controller,
            'Plain action failed',
            exceptionRequestService: FakeServiceRegistry::get('request'),
            exceptionErrorService: $this->errorService,
            classNameResolver: static fn(object $object): string => 'resolved-' . get_class($object)
        );

        $this->assertCount(1, $this->errorService->exceptionMessages);
        $this->assertStringContainsString('Plain controller fatal error (resolved-' . get_class($controller) . '). Plain action failed', $this->errorService->exceptionMessages[0][0]);
    }

    public function testPlainFatalSourceUsesInjectedClassNameResolver(): void
    {
        $source = file_get_contents(__DIR__ . '/../../../../core/exception/plain/fatal.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('?callable $classNameResolver = null', $source);
        $this->assertStringContainsString('private function className(object $object, \Closure $classNameResolver): string', $source);
        $this->assertStringContainsString('$this->className($controller, $classNameResolver)', $source);
        $this->assertStringNotContainsString('get_class_alt(', $source);
    }
}
