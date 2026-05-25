<?php

declare(strict_types=1);

use fan\core\base\service\single;
use fan\core\service\service_single_state;
use FanTest\_core\SourceFileContractTestCase;
use fan\core\base\service;
use fan\core\exception\service\fatal;
use fan\project\exception\service\fatal as service_fatal;


if (!function_exists('fan\core\base\get_class_name')) {
    eval('
        namespace fan\core\base;

        function get_class_name(string|object $object): ?string
        {
            if (is_object($object)) {
                $object = get_class($object);
            }
            $parts = explode("\\\\", $object);

            return end($parts);
        }
    ');
}

class BaseServiceSingleTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/base/service/single.php';

    public function testSingleServiceIsSingleton(): void
    {
        $this->assertTrue((new BaseServiceSingleProbe())->isSingleton());
    }

    public function testSingleServiceDoesNotExposeLegacyStaticInstanceAccessor(): void
    {
        $reflection = new \ReflectionClass(single::class);

        $this->assertFalse($reflection->hasMethod('instance'));
    }

    public function testSingleServiceDoesNotStoreInstancesInStaticProperty(): void
    {
        $source = $this->sourceCode();

        $this->assertStringNotContainsString('private static ?array $instances', $source);
        $this->assertStringNotContainsString('self::$instances', $source);
    }

    public function testSingleServiceDuplicateGuardUsesInjectedState(): void
    {
        $state = new service_single_state();
        $runtime = new BaseServiceSingleRuntimeDouble($state);
        $configurator = new BaseServiceSingleConfiguratorDouble();

        $first = new BaseServiceSingleInitializedProbe($runtime, $configurator);

        $this->assertSame($first, $state->getInstance(BaseServiceSingleInitializedProbe::class));

        $this->expectException(fatal::class);
        $this->expectExceptionMessage('Dublicate of service init "' . BaseServiceSingleInitializedProbe::class . '"');

        new BaseServiceSingleInitializedProbe($runtime, $configurator);
    }

    public function testSingleServiceRequiresInjectedStateForInstanceStorage(): void
    {
        $service = new BaseServiceSingleProbe();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Service single state is not configured for BaseServiceSingleProbe.');

        $service->exposedSaveInstance();
    }
}

final class BaseServiceSingleProbe extends single
{
    public function __construct()
    {
    }

    public function exposedSaveInstance(): static
    {
        return $this->_saveInstance();
    }
}

final class BaseServiceSingleInitializedProbe extends single
{
    public function __construct(object $runtime, object $configurator)
    {
        parent::__construct(false, $runtime, $configurator, static fn(): object => new \stdClass());
    }

    public function getExceptionLogType(): string
    {
        return 'nothing';
    }
}

final class BaseServiceSingleRuntimeDouble
{
    public function __construct(private readonly service_single_state $singleState)
    {
    }

    public function serviceSingleState(): service_single_state
    {
        return $this->singleState;
    }

    public function serviceExceptionFactory(): callable
    {
        return static fn(
            string $exceptionClass,
            service $service,
            string $message,
            int $code = E_USER_ERROR,
            ?\Throwable $previous = null
        ): \Throwable => new service_fatal($service, $message, $code, $previous);
    }
}

final class BaseServiceSingleConfiguratorDouble
{
    public function getServiceConfig(object $service): object
    {
        return new class {
            public function get(string $key, mixed $default = null): mixed
            {
                return $default;
            }
        };
    }

    public function reset(string $serviceName, string $key): void
    {
    }
}
