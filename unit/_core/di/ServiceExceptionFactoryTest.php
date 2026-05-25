<?php

declare(strict_types=1);

use fan\core\di\service_exception_factory;
use PHPUnit\Framework\TestCase;
use fan\core\base\service;
use fan\core\exception\service\fatal;


require_once __DIR__ . '/../../../_core/factory/service_exception_factory.php';
require_once __DIR__ . '/../../../_core/base/service.php';
require_once __DIR__ . '/../../../_core/exception/base.php';
require_once __DIR__ . '/../../../_core/exception/service/fatal.php';

final class ServiceExceptionFactoryTest extends TestCase
{
    public function testFactoryCreatesProjectServiceFatalException(): void
    {
        if (!class_exists('\fan\project\exception\service\fatal', false)) {
            class_alias(fatal::class, '\fan\project\exception\service\fatal');
        }

        $service = (new ReflectionClass(ServiceExceptionFactoryServiceDouble::class))->newInstanceWithoutConstructor();
        $previous = new RuntimeException('previous');

        $exception = (new service_exception_factory())(
            '\fan\project\exception\service\fatal',
            $service,
            'broken service',
            E_USER_WARNING,
            $previous
        );

        $this->assertInstanceOf(fatal::class, $exception);
        $this->assertSame($service, $exception->getService());
        $this->assertSame('broken service', $exception->getMessage());
        $this->assertSame(E_USER_WARNING, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testFactoryDoesNotCascadeWhenServiceLoggerDependenciesAreMissing(): void
    {
        if (!class_exists('\fan\project\exception\service\fatal', false)) {
            class_alias(fatal::class, '\fan\project\exception\service\fatal');
        }

        $service = (new ReflectionClass(ServiceExceptionFactoryLoggingServiceDouble::class))->newInstanceWithoutConstructor();

        $exception = (new service_exception_factory())(
            '\fan\project\exception\service\fatal',
            $service,
            'early service failure'
        );

        $this->assertInstanceOf(fatal::class, $exception);
        $this->assertSame('early service failure', $exception->getMessage());
    }

    public function testFactoryRejectsUnsupportedServiceExceptionClass(): void
    {
        $service = (new ReflectionClass(ServiceExceptionFactoryServiceDouble::class))->newInstanceWithoutConstructor();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported service exception class');

        (new service_exception_factory())('\fan\project\exception\fatal', $service, 'broken');
    }}

final class ServiceExceptionFactoryServiceDouble extends service
{
    public function isSingleton(): bool
    {
        return false;
    }

    public function getExceptionLogType(): string
    {
        return 'nothing';
    }
}

final class ServiceExceptionFactoryLoggingServiceDouble extends service
{
    public function isSingleton(): bool
    {
        return false;
    }

    public function getExceptionLogType(): string
    {
        return 'service';
    }
}
