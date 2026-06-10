<?php

declare(strict_types=1);

use fan\core\di\error500_exception_factory;
use FanTest\core\exception\FakeErrorService;
use FanTest\core\exception\FakeRequestService;
use PHPUnit\Framework\TestCase;
use fan\project\exception\error500;


require_once __DIR__ . '/../../mock/core/exception/ExceptionDoubles.php';

final class Error500ExceptionFactoryTest extends TestCase
{
    public function testFactoryCreatesProjectError500ExceptionWithInjectedDependencies(): void
    {
        $errorService = new FakeErrorService();
        $headerWriter = new Error500ExceptionFactoryHeaderWriterDouble();
        $previous = new RuntimeException('previous');

        $exception = (new error500_exception_factory())->setExceptionDependencies(
            exceptionRequestService: new FakeRequestService(),
            exceptionErrorService: $errorService,
            exceptionHeaderWriter: $headerWriter
        )(
            'View parser cannot define a format.',
            E_USER_WARNING,
            $previous
        );

        $this->assertInstanceOf(error500::class, $exception);
        $this->assertSame(E_USER_WARNING, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
        $this->assertSame(['HTTP/1.1 500 Internal Server Error'], $headerWriter->headers);
        $this->assertSame(
            [['View parser cannot define a format.', 'Error 500', 'GET /unit-test']],
            $errorService->exceptionMessages
        );
    }}

final class Error500ExceptionFactoryHeaderWriterDouble
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
