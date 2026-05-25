<?php

declare(strict_types=1);

use fan\core\di\application_service_factory;
use fan\core\service\application;
use PHPUnit\Framework\TestCase;
use fan\core\service\service_listener_state;
use fan\core\service\service_single_state;
use fan\project\service\application as service_application;

final class ApplicationServiceFactoryTest extends TestCase
{
    public function testFactoryCreatesCoreApplicationServiceWithoutDynamicOverrideFactory(): void
    {
        $this->ensureBaseFunctionAliases();

        $runtime = new ApplicationServiceFactoryRuntimeDouble();
        $config = new ApplicationServiceFactoryConfigDouble();
        $configurator = new ApplicationServiceFactoryConfiguratorDouble($config);
        $cacheFactory = static fn(string $type): object => (object)['type' => $type];
        $arrayAdducer = static fn(mixed $value): array => is_array($value) ? $value : [$value];
        $overrideCalls = [];

        $application = (new application_service_factory(
            static function (string $className, array $arguments) use (&$overrideCalls): object {
                $overrideCalls[] = [$className, $arguments];

                return new stdClass();
            }
        ))(
            application::class,
            true,
            $runtime,
            $runtime,
            $configurator,
            $cacheFactory,
            $arrayAdducer
        );

        $this->assertInstanceOf(application::class, $application);
        $this->assertSame([], $overrideCalls);
        $this->assertSame([application::class], $runtime->initializer->serviceParams);
        $this->assertSame([$application], $configurator->getServiceConfigCalls);
        $this->assertContains($configurator->resetCalls[0][0] ?? null, ['application', application::class]);
        $this->assertSame('ENABLED', $configurator->resetCalls[0][1] ?? null);
        $this->assertSame('frontend', $application->getDefaultAppName());
        $this->assertSame('PHP-FAN', $application->getProjectName());
    }

    public function testFactoryDelegatesConfiguredOverrideServiceCreation(): void
    {
        $runtime = new stdClass();
        $configurator = new stdClass();
        $cacheFactory = static fn(string $type): object => (object)['type' => $type];
        $arrayAdducer = static fn(mixed $value): array => is_array($value) ? $value : [$value];
        $configuredCalls = [];

        $application = (new application_service_factory(
            static function (string $className, array $arguments) use (&$configuredCalls): object {
                $configuredCalls[] = [$className, $arguments];

                return new $className(...$arguments);
            }
        ))(
            ApplicationServiceFactoryProbe::class,
            true,
            $runtime,
            $runtime,
            $configurator,
            $cacheFactory,
            $arrayAdducer
        );

        $this->assertInstanceOf(ApplicationServiceFactoryProbe::class, $application);
        $this->assertTrue($application->allowIni);
        $this->assertSame($runtime, $application->runtime);
        $this->assertSame($runtime, $application->serviceBootstrapRuntime);
        $this->assertSame($configurator, $application->serviceConfigurator);
        $this->assertSame($cacheFactory, $application->serviceCacheFactory);
        $this->assertSame($arrayAdducer, $application->arrayAdducer);
        $this->assertSame(ApplicationServiceFactoryProbe::class, $configuredCalls[0][0] ?? null);
        $this->assertSame([true, $runtime, $runtime, $configurator, $cacheFactory, $arrayAdducer], $configuredCalls[0][1] ?? null);
    }

                private function ensureBaseFunctionAliases(): void
    {
        if (!function_exists('get_class_name')) {
            eval('function get_class_name(string|object $object): ?string { if (is_object($object)) { $object = get_class($object); } $parts = explode("\\\\\\\\", $object); return end($parts); }');
        }
        if (!function_exists('fan\core\base\get_class_name')) {
            eval('namespace fan\core\base { function get_class_name(string|object $object): ?string { return \get_class_name($object); } }');
        }
        if (!function_exists('adduceToArray')) {
            eval('function adduceToArray(mixed $value): array { if (is_array($value)) { return $value; } if (is_object($value) && method_exists($value, "toArray")) { return $value->toArray(); } return empty($value) ? [] : [$value]; }');
        }
    }
}

final class ApplicationServiceFactoryProbe
{
    public function __construct(
        public bool $allowIni,
        public object $runtime,
        public object $serviceBootstrapRuntime,
        public object $serviceConfigurator,
        public $serviceCacheFactory,
        public $arrayAdducer
    ) {
    }
}


final class ApplicationServiceFactoryRuntimeDouble
{
    public ApplicationServiceFactoryInitializerDouble $initializer;

    public function __construct()
    {
        $this->initializer = new ApplicationServiceFactoryInitializerDouble();
    }

    public function getInitializer(): ApplicationServiceFactoryInitializerDouble
    {
        return $this->initializer;
    }

    private ?service_listener_state $baseServiceListenerState = null;

    private ?service_single_state $baseServiceSingleState = null;

    public function serviceListenerState(): service_listener_state
    {
        return $this->baseServiceListenerState ??= new service_listener_state();
    }

    public function serviceSingleState(): service_single_state
    {
        return $this->baseServiceSingleState ??= new service_single_state();
    }

    public function classNameResolver(): callable
    {
        return static fn(object $object): string => get_class($object);
    }
}

final class ApplicationServiceFactoryInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class ApplicationServiceFactoryConfigDouble
{
    private array $values = [
        'used_names' => ['frontend', 'admin'],
        'used_app' => ['frontend'],
        'PROJECT_NAME' => 'PHP-FAN',
    ];

    public function get(mixed $key = null, mixed $default = null): mixed
    {
        return $key === null ? $this->values : ($this->values[$key] ?? $default);
    }
}

final class ApplicationServiceFactoryConfiguratorDouble
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
