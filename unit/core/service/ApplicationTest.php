<?php

declare(strict_types=1);

require_once __DIR__ . '/../../mock/core/base/DataFunctions.php';

use fan\core\service\application;
use FanTest\core\SourceFileContractTestCase;
use fan\core\base\service;
use fan\core\service\service_listener_state;
use fan\core\service\service_single_state;


class ServiceApplicationTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/service/application.php';

    public function testConfiguredDefaultAppNameFallsBackToFirstUsedApp(): void
    {
        $application = $this->application([
            'used_app' => ['frontend', 'admin'],
        ]);

        $this->assertSame('frontend', $application->getDefaultAppName());
    }

    public function testConfiguredDefaultAppNameOverridesUsedAppFallback(): void
    {
        $application = $this->application([
            'used_app' => ['frontend', 'admin'],
            'default_app' => 'admin',
        ]);

        $this->assertSame('admin', $application->getDefaultAppName());
    }

    public function testProjectNameFallsBackWhenNotConfigured(): void
    {
        $this->assertSame('Name of project is not set', $this->application()->getProjectName());
        $this->assertSame('Fan Project', $this->application(['PROJECT_NAME' => 'Fan Project'])->getProjectName());
    }

    public function testAppNameAndCoreVersionAccessors(): void
    {
        $application = $this->application();
        $property = new ReflectionProperty(application::class, 'name');
        $property->setValue($application, 'frontend');

        $this->assertSame('frontend', $application->getAppName());
        $this->assertSame('PHP-FAN 05.02.011 (2015-10-03)', $application->getCoreVersion());
    }

    public function testSetAppNameDefinesNewApplicationThroughInjectedRuntime(): void
    {
        $this->ensureBroadcastHelper();
        $runtime = new ServiceApplicationRuntimeDouble();
        $application = $this->application(runtime: $runtime);
        $usedNames = new ReflectionProperty(application::class, 'usedNames');
        $usedNames->setValue($application, ['frontend']);

        $this->assertSame($application, $application->setAppName('frontend'));
        $this->assertSame('frontend', $application->getAppName());
        $this->assertSame([$application], $runtime->loader->definedApps);
    }

    public function testConstructorUsesInjectedBaseServiceDependencies(): void
    {
        $this->ensureBroadcastHelper();
        $runtime = new ServiceApplicationRuntimeDouble();
        $config = new ServiceApplicationConfigDouble([
            'used_names' => 'frontend',
        ]);
        $configurator = new ServiceApplicationConfiguratorDouble($config);
        $cacheFactoryCalls = [];
        $arrayAdducerCalls = [];

        $application = new ServiceApplicationConstructorProbe(
            true,
            $runtime,
            $runtime,
            $configurator,
            static function (string $type) use (&$cacheFactoryCalls): object {
                $cacheFactoryCalls[] = $type;

                return (object)['type' => $type];
            },
            static function (mixed $value) use (&$arrayAdducerCalls): array {
                $arrayAdducerCalls[] = $value;

                return is_array($value) ? $value : [$value];
            }
        );

        $this->assertSame([ServiceApplicationConstructorProbe::class], $runtime->initializer->serviceParams);
        $this->assertSame([$application], $configurator->getServiceConfigCalls);
        $this->assertSame([
            [ServiceApplicationConstructorProbe::class, 'ENABLED'],
        ], $configurator->resetCalls);
        $this->assertSame([], $cacheFactoryCalls);
        $this->assertSame(['frontend'], $arrayAdducerCalls);

        $usedNames = new ReflectionProperty(application::class, 'usedNames');
        $this->assertSame(['frontend'], $usedNames->getValue($application));
    }

    public function testConstructorRequiresInjectedArrayAdducer(): void
    {
        $runtime = new ServiceApplicationRuntimeDouble();
        $configurator = new ServiceApplicationConfiguratorDouble(
            new ServiceApplicationConfigDouble(['used_names' => 'frontend'])
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Array adducer is not configured for application service.');

        new ServiceApplicationConstructorProbe(
            true,
            $runtime,
            $runtime,
            $configurator,
            static fn(string $type): object => (object)['type' => $type],
            null
        );
    }

    public function testInvalidAppNameUsesInjectedServiceExceptionFactory(): void
    {
        $runtime = new ServiceApplicationRuntimeDouble();
        $application = $this->application(runtime: $runtime);

        try {
            $application->setAppName('');
            $this->fail('Expected service exception factory throwable.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Application name can\'t be empty.', $exception->getMessage());
        }

        $this->assertSame([
            ['\fan\project\exception\service\fatal', $application, 'Application name can\'t be empty.', E_USER_ERROR, null],
        ], $runtime->serviceExceptionFactoryCalls);
    }

    private function ensureBroadcastHelper(): void
    {
        if (function_exists('fan\core\base\get_class_name')) {
            return;
        }

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

    private function application(array $config = [], ?object $runtime = null): application
    {
        $application = (new ReflectionClass(application::class))->newInstanceWithoutConstructor();
        $property = new ReflectionProperty(service::class, 'config');
        $property->setValue($application, new ServiceApplicationConfigDouble($config));
        $runtimeProperty = new ReflectionProperty(application::class, 'runtime');
        $runtimeProperty->setValue($application, $runtime);
        if ($runtime !== null) {
            $application->setServiceDependencies(serviceBootstrapRuntime: $runtime);
        }

        return $application;
    }
}

final class ServiceApplicationConfigDouble
{
    public function __construct(private array $data = [])
    {
    }

    public function get(mixed $key = null, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }
}

final class ServiceApplicationConstructorProbe extends application
{
    public function __construct(
        bool $allowIni,
        ?object $runtime,
        ?object $serviceBootstrapRuntime,
        ?object $serviceConfigurator,
        ?callable $serviceCacheFactory,
        ?callable $arrayAdducer
    )
    {
        parent::__construct($allowIni, $runtime, $serviceBootstrapRuntime, $serviceConfigurator, $serviceCacheFactory, $arrayAdducer);
    }
}

final class ServiceApplicationRuntimeDouble
{
    public ServiceApplicationLoaderDouble $loader;
    public ServiceApplicationInitializerDouble $initializer;
    public array $serviceExceptionFactoryCalls = [];

    public function __construct()
    {
        $this->loader = new ServiceApplicationLoaderDouble();
        $this->initializer = new ServiceApplicationInitializerDouble();
    }

    public function getLoader(): ServiceApplicationLoaderDouble
    {
        return $this->loader;
    }

    public function getInitializer(): ServiceApplicationInitializerDouble
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

            return new RuntimeException($message, $code, $previous);
        };
    }
}

final class ServiceApplicationLoaderDouble
{
    public array $definedApps = [];

    public function defineNewApp(object $application): void
    {
        $this->definedApps[] = $application;
    }
}

final class ServiceApplicationInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class ServiceApplicationConfiguratorDouble
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
