<?php

declare(strict_types=1);

use fan\core\service\tab\engine;
use FanTest\_core\SourceFileContractTestCase;
use fan\core\base\service;


class ServiceTabEngineTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/service/tab/engine.php';

    public function testSetFacadeStoresOnlyFirstFacade(): void
    {
        $engine = new ServiceTabEngineProbe();
        $firstFacade = new ServiceTabFacadeDouble();
        $secondFacade = new ServiceTabFacadeDouble();

        $this->assertSame($engine, $engine->setFacade($firstFacade));
        $this->assertSame($engine, $engine->setFacade($secondFacade));
        $this->assertSame($firstFacade, $engine->facade());
    }

    public function testMakeExceptionUsesFacadeServiceExceptionFactory(): void
    {
        $calls = [];
        $facade = new ServiceTabEngineFacadeDouble(
            static function (
                string $message,
                int $code,
                ?Throwable $previous
            ) use (&$calls): Throwable {
                $calls[] = [$message, $code, $previous];

                return new RuntimeException('factory: ' . $message, 0, $previous);
            }
        );
        $engine = new ServiceTabEngineProbe();
        $engine->setFacade($facade);

        try {
            $engine->raise('bad listener');
            $this->fail('Expected facade service exception factory to provide the throwable.');
        } catch (RuntimeException $exception) {
            $this->assertSame('factory: bad listener', $exception->getMessage());
        }

        $this->assertSame([['bad listener', E_USER_ERROR, null]], $calls);
    }

    public function testSourceUsesFacadeServiceExceptionBoundary(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('createServiceFatalExceptionForSubObject', $source);
        $this->assertStringNotContainsString('new \fan\project\exception\service\fatal', $source);
    }
}

final class ServiceTabEngineProbe extends engine
{
    public function __construct()
    {
    }

    public function facade(): ?object
    {
        return $this->facade;
    }

    public function raise(mixed $message): never
    {
        $this->_makeException($message);
    }
}

final class ServiceTabEngineFacadeDouble extends service
{
    public function __construct(private $exceptionFactory)
    {
    }

    public function isSingleton(): bool
    {
        return false;
    }

    public function createServiceFatalExceptionForSubObject(string $message, int $code = E_USER_ERROR, ?Throwable $previous = null): Throwable
    {
        return ($this->exceptionFactory)($message, $code, $previous);
    }
}

class ServiceTabFacadeDouble extends service
{
    public function __construct(private mixed $configDouble = null)
    {
    }

    public function isSingleton(): bool
    {
        return false;
    }

    public function getConfig($key = null, $default = null): mixed
    {
        if ($key === null) {
            return $this->configDouble;
        }

        return is_object($this->configDouble) && method_exists($this->configDouble, 'get')
            ? $this->configDouble->get($key, $default)
            : $default;
    }
}
