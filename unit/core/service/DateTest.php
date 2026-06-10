<?php

declare(strict_types=1);

use fan\core\service\date as DateService;
use FanTest\core\SourceFileContractTestCase;
use fan\core\base\service;
use fan\core\service\date_state;


class ServiceDateTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/service/date.php';

    public function testGetCustomAndStringUseConfiguredFullPattern(): void
    {
        $date = $this->dateService(new DateTime('2026-05-26 12:34:56', new DateTimeZone('UTC')));

        $this->assertSame('2026-05-26 12:34:56', $date->get());
        $this->assertSame('2026', $date->getCustom('Y'));
        $this->assertSame('2026-05-26 12:34:56', (string)$date);
        $this->assertTrue($date->isTime());
    }

    public function testDateOnlyModeUsesShortPatternAndExportsPatternParts(): void
    {
        $date = $this->dateService(
            new DateTime('2026-05-26 12:34:56', new DateTimeZone('UTC')),
            isTime: false
        );

        $this->assertSame('2026-05-26', $date->get());
        $this->assertFalse($date->isTime());
        $this->assertSame([
            'Y' => '2026',
            'm' => '05',
            'd' => '26',
        ], $date->getDateAsArray());
        $this->assertSame($date->getDateAsArray(), $date->toArray());
    }

    public function testSetFormatChangesUnsavedInstanceToKnownFormat(): void
    {
        $date = $this->dateService(
            new DateTime('2026-05-26 12:34:56', new DateTimeZone('UTC')),
            save: false
        );

        $this->assertSame($date, $date->setFormat('display'));

        $this->assertSame('26/05/2026 12:34', $date->get());
    }

    public function testSetFormatOnSavedInstanceUsesInjectedServiceExceptionFactory(): void
    {
        $runtime = new ServiceDateRuntimeDouble();
        $date = $this->dateService(
            new DateTime('2026-05-26 12:34:56', new DateTimeZone('UTC')),
            runtime: $runtime
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('You can change format only for not saved date.');

        try {
            $date->setFormat('display');
        } finally {
            $this->assertSame([
                ['\fan\project\exception\service\fatal', $date, 'You can change format only for not saved date.', E_USER_ERROR, null],
            ], $runtime->serviceExceptionFactoryCalls);
        }
    }

    public function testTimestampAndShiftDateUseStoredDate(): void
    {
        $date = $this->dateService(new DateTime('2026-05-26 12:34:56', new DateTimeZone('UTC')));

        $this->assertSame(1779798896, $date->getTimeStamp());
        $this->assertSame('2026-05-26 13:34:56', $date->shiftDate(3600));
    }

    public function testDifferenceUsesInjectedDateFactory(): void
    {
        $calls = [];
        $date = $this->dateService(
            new DateTime('2026-05-26 12:34:56', new DateTimeZone('UTC')),
            dateFactory: function (?string $date, mixed $format, mixed $timezone, bool $save) use (&$calls): DateService {
                $calls[] = [$date, $format, $timezone, $save];

                return $this->dateService(
                    new DateTime('2026-05-26 12:33:56', new DateTimeZone('UTC')),
                    format: (string)$format,
                    timezone: (string)$timezone,
                    save: $save
                );
            }
        );

        $this->assertSame(60, $date->getDifference('2026-05-26 12:33:56'));
        $this->assertSame([['2026-05-26 12:33:56', 'mysql', 'UTC', true]], $calls);
    }

    public function testConstructorUsesInjectedBaseServiceDependencies(): void
    {
        $this->ensureBaseFunctionAliases();

        $runtime = new ServiceDateRuntimeDouble();
        $config = $this->configDouble();
        $configurator = new ServiceDateConfiguratorDouble($config);
        $cacheFactoryCalls = [];

        $date = new ServiceDateConstructorProbe(
            new DateTime('2026-05-26 12:34:56', new DateTimeZone('UTC')),
            'mysql',
            true,
            'UTC',
            false,
            new date_state(),
            null,
            $runtime,
            $configurator,
            static function (string $type) use (&$cacheFactoryCalls): object {
                $cacheFactoryCalls[] = $type;

                return (object)['type' => $type];
            },
            static fn(object $object): string => get_class($object),
            static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default
        );

        $this->assertSame([ServiceDateConstructorProbe::class], $runtime->initializer->serviceParams);
        $this->assertSame([$date], $configurator->getServiceConfigCalls);
        $this->assertSame([
            [ServiceDateConstructorProbe::class, 'ENABLED'],
        ], $configurator->resetCalls);
        $this->assertSame('2026-05-26 12:34:56', $date->get());
        $this->assertSame([], $cacheFactoryCalls);
    }

    public function testModifyCarriesInjectedBaseDependenciesToNewDateInstance(): void
    {
        $this->ensureBaseFunctionAliases();

        $runtime = new ServiceDateRuntimeDouble();
        $configurator = new ServiceDateConfiguratorDouble($this->configDouble());
        $dateInstanceCalls = [];
        $dateInstanceFactory = null;
        $dateInstanceFactory = function (
            DateTime $date,
            mixed $format,
            bool $isTime,
            mixed $timezone,
            bool $save,
            ?object $state,
            ?callable $dateFactory,
            ?object $serviceBootstrapRuntime,
            ?object $serviceConfigurator,
            ?callable $serviceCacheFactory,
            ?callable $classNameResolver = null,
            ?callable $arrayValueReader = null
        ) use (&$dateInstanceFactory, &$dateInstanceCalls): ServiceDateConstructorProbe {
            $dateInstanceCalls[] = [$date, $format, $isTime, $timezone, $save, $state];

            return new ServiceDateConstructorProbe(
                $date,
                $format,
                $isTime,
                $timezone,
                $save,
                $state,
                $dateFactory,
                $serviceBootstrapRuntime,
                $serviceConfigurator,
                $serviceCacheFactory,
                $classNameResolver,
                $arrayValueReader,
                $dateInstanceFactory
            );
        };
        $date = new ServiceDateConstructorProbe(
            new DateTime('2026-05-26 12:34:56', new DateTimeZone('UTC')),
            'mysql',
            true,
            'UTC',
            false,
            new date_state(),
            null,
            $runtime,
            $configurator,
            static fn(string $type): object => (object)['type' => $type],
            static fn(object $object): string => get_class($object),
            static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default,
            $dateInstanceFactory
        );

        $modified = $date->modify('+1 hour');

        $this->assertSame('2026-05-26 13:34:56', $modified->get());
        $this->assertCount(1, $dateInstanceCalls);
        $this->assertSame('2026-05-26 13:34:56', $dateInstanceCalls[0][0]->format('Y-m-d H:i:s'));
        $this->assertSame(['mysql', true, 'UTC', false], array_slice($dateInstanceCalls[0], 1, 4));
        $this->assertCount(2, $configurator->getServiceConfigCalls);
        $this->assertContainsOnlyInstancesOf(ServiceDateConstructorProbe::class, $configurator->getServiceConfigCalls);
        $this->assertSame([
            ServiceDateConstructorProbe::class,
            ServiceDateConstructorProbe::class,
        ], $runtime->initializer->serviceParams);
    }

    private function dateService(
        DateTime $date,
        string $format = 'mysql',
        bool $isTime = true,
        string $timezone = 'UTC',
        bool $save = true,
        ?callable $dateFactory = null,
        ?object $runtime = null
    ): DateService {
        $service = (new ReflectionClass(DateService::class))->newInstanceWithoutConstructor();

        foreach ([
            'date' => $date,
            'format' => $format,
            'isTime' => $isTime,
            'timezone' => $timezone,
            'save' => $save,
            'state' => new date_state(),
            'dateFactory' => $dateFactory,
        ] as $propertyName => $value) {
            $property = new ReflectionProperty(DateService::class, $propertyName);
            $property->setValue($service, $value);
        }

        $property = new ReflectionProperty(service::class, 'config');
        $property->setValue($service, new ServiceDateConfigDouble([
            'FORMAT' => [
                'mysql' => [
                    'full_pattern' => 'Y-m-d H:i:s',
                    'short_pattern' => 'Y-m-d',
                ],
                'display' => [
                    'full_pattern' => 'd/m/Y H:i',
                    'short_pattern' => 'd/m/Y',
                ],
            ],
        ]));
        if ($runtime !== null) {
            $service->setServiceDependencies(serviceBootstrapRuntime: $runtime);
        }

        return $service;
    }

    private function configDouble(): ServiceDateConfigDouble
    {
        return new ServiceDateConfigDouble([
            'FORMAT' => [
                'mysql' => [
                    'full_pattern' => 'Y-m-d H:i:s',
                    'short_pattern' => 'Y-m-d',
                ],
                'display' => [
                    'full_pattern' => 'd/m/Y H:i',
                    'short_pattern' => 'd/m/Y',
                ],
            ],
        ]);
    }

    private function ensureBaseFunctionAliases(): void
    {
        if (!function_exists('get_class_name')) {
            eval('function get_class_name(string|object $object): ?string { if (is_object($object)) { $object = get_class($object); } $parts = explode("\\\\\\\\", $object); return end($parts); }');
        }
        if (!function_exists('fan\core\base\get_class_name')) {
            eval('namespace fan\core\base { function get_class_name(string|object $object): ?string { return \get_class_name($object); } }');
        }
    }
}

final class ServiceDateConstructorProbe extends DateService
{
    public function __construct(
        DateTime $date,
        mixed $format,
        bool $isTime,
        mixed $timezone,
        bool $save,
        ?object $state,
        ?callable $dateFactory,
        ?object $serviceBootstrapRuntime,
        ?object $serviceConfigurator,
        ?callable $serviceCacheFactory,
        ?callable $classNameResolver = null,
        ?callable $arrayValueReader = null,
        ?callable $dateInstanceFactory = null
    )
    {
        parent::__construct(
            $date,
            $format,
            $isTime,
            $timezone,
            $save,
            $state,
            $dateFactory,
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory,
            $classNameResolver,
            $arrayValueReader,
            $dateInstanceFactory
        );
    }
}

final class ServiceDateRuntimeDouble
{
    public ServiceDateInitializerDouble $initializer;
    public array $serviceExceptionFactoryCalls = [];

    public function __construct()
    {
        $this->initializer = new ServiceDateInitializerDouble();
    }

    public function getInitializer(): ServiceDateInitializerDouble
    {
        return $this->initializer;
    }

    public function serviceExceptionFactory(): callable
    {
        return function (
            string $exceptionClass,
            service $service,
            string $message,
            int $code = E_USER_ERROR,
            ?\Throwable $previous = null
        ): \Throwable {
            $this->serviceExceptionFactoryCalls[] = [$exceptionClass, $service, $message, $code, $previous];

            return new \RuntimeException($message, $code, $previous);
        };
    }
}

final class ServiceDateInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class ServiceDateConfiguratorDouble
{
    public array $getServiceConfigCalls = [];
    public array $resetCalls = [];

    public function __construct(private object $config)
    {
    }

    public function getServiceConfig(object $service): object
    {
        $this->getServiceConfigCalls[] = $service;

        return $this->config;
    }

    public function reset(string $className, string $key): void
    {
        $this->resetCalls[] = [$className, $key];
    }
}

final class ServiceDateConfigDouble extends ArrayObject
{
    public function __construct(array $data)
    {
        parent::__construct($data);
    }

    public function get(mixed $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this;
        }

        $value = $this->getArrayCopy();
        foreach ((array)$key as $part) {
            if (!is_array($value) || !array_key_exists($part, $value)) {
                return $default;
            }
            $value = $value[$part];
        }

        return $value;
    }
}
