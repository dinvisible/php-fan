<?php

declare(strict_types=1);

use FanTest\core\block\FakeServiceRegistry;
use FanTest\core\exception\FakeErrorService;
use FanTest\core\exception\FakeRequestService;
use fan\core\exception\error500;
use PHPUnit\Framework\TestCase;


require_once __DIR__ . '/../../mock/core/exception/ExceptionDoubles.php';
require_once __DIR__ . '/../../../core/exception/base.php';
require_once __DIR__ . '/../../../core/exception/error500.php';

class ExceptionError500Test extends TestCase
{
    private FakeErrorService $errorService;

    protected function setUp(): void
    {
        FakeServiceRegistry::reset();

        $this->errorService = new FakeErrorService();
        FakeServiceRegistry::set('request', new FakeRequestService());
        FakeServiceRegistry::set('error', $this->errorService);
    }

    public function testError500KeepsInternalMessageAndLogsThroughErrorService(): void
    {
        $headerWriter = new ExceptionHeaderWriterDouble();
        $exception = new error500(
            'controller exploded',
            E_USER_WARNING,
            exceptionRequestService: FakeServiceRegistry::get('request'),
            exceptionErrorService: $this->errorService,
            exceptionHeaderWriter: $headerWriter
        );

        $this->assertSame(['HTTP/1.1 500 Internal Server Error'], $headerWriter->headers);
        $this->assertSame('controller exploded', $exception->getMessage());
        $this->assertSame(E_USER_WARNING, $exception->getCode());
        $this->assertSame('controller exploded', $exception->getMessageForLog());
        $this->assertSame('Please visit the site later.', $exception->getMessageForShow());
        $this->assertSame('error_500', $exception->getErrorFile());
        $this->assertNull($exception->getDbOper());
        $this->assertSame([], FakeServiceRegistry::get('database_connections')->calls);
        $this->assertSame(
            [['controller exploded', 'Error 500', 'GET /unit-test']],
            $this->errorService->exceptionMessages
        );
    }

    public function testError500SourceDoesNotCallNativeHeaderDirectly(): void
    {
        $source = file_get_contents(__DIR__ . '/../../../core/exception/error500.php');

        $this->assertIsString($source);
        $this->assertStringNotContainsString('headers_sent(', $source);
        $this->assertStringNotContainsString('header(', $source);
        $this->assertStringContainsString('sendInternalServerErrorHeader()', $source);
    }
}

final class ExceptionHeaderWriterDouble
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
