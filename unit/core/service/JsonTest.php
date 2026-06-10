<?php

declare(strict_types=1);

use fan\core\service\json;
use FanTest\core\SourceFileContractTestCase;

class ServiceJsonTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/service/json.php';

    public function testEncodeAndDecodeUseInternalJsonFunctions(): void
    {
        $service = $this->jsonService();

        $json = $service->encode(['name' => 'fan', 'enabled' => true]);

        $this->assertSame('{"name":"fan","enabled":true}', $json);
        $this->assertFalse($service->isError());
        $this->assertSame(JSON_ERROR_NONE, $service->getError());
        $this->assertSame(['name' => 'fan', 'enabled' => true], $service->decode((string)$json));
        $this->assertFalse($service->isError());
    }

    public function testBase64ModeEncodesAndDecodesScalarArrayValues(): void
    {
        $service = $this->jsonService(useBase64: true);

        $json = $service->encode(['name' => 'fan', 'nested' => ['value' => 42]]);

        $this->assertSame('{"name":"ZmFu","nested":{"value":"NDI="}}', $json);
        $this->assertSame(['name' => 'fan', 'nested' => ['value' => '42']], $service->decode((string)$json));
    }

    public function testDecodeTracksSyntaxErrors(): void
    {
        $service = $this->jsonService();

        $this->assertNull($service->decode('{bad json'));

        $this->assertTrue($service->isError());
        $this->assertSame(JSON_ERROR_SYNTAX, $service->getError());
        $this->assertSame('Syntax error', $service->getErrorText());
    }

    public function testPrettyPrintFormatsCompactJson(): void
    {
        $service = $this->jsonService();

        $this->assertSame(
            "{\n  \"items\":[\n    1,\n    2\n  ],\n  \"ok\":true\n}",
            $service->prettyPrint('{"items":[1,2],"ok":true}', '  ')
        );
    }

    public function testEncodeLogsJsonErrorsThroughInjectedErrorFactory(): void
    {
        $error = new ServiceJsonErrorDouble();
        $service = $this->jsonService(errorFactory: static fn(): object => $error);
        $resource = fopen('php://memory', 'r');
        $this->assertIsResource($resource);

        $this->assertFalse($service->encode($resource));
        fclose($resource);

        $this->assertTrue($service->isError());
        $this->assertSame([['No error has occurred', 'JSON error', '', true, false]], $error->messages);
    }

    public function testConstructorUsesInjectedBaseServiceDependencies(): void
    {
        $this->ensureBaseFunctionAliases();

        $runtime = new ServiceJsonRuntimeDouble();
        $config = new ServiceJsonConfigDouble();
        $configurator = new ServiceJsonConfiguratorDouble($config);
        $cacheFactoryCalls = [];

        $json = new ServiceJsonConstructorProbe(
            true,
            static fn(): object => new ServiceJsonErrorDouble(),
            $runtime,
            $configurator,
            static function (string $type) use (&$cacheFactoryCalls): object {
                $cacheFactoryCalls[] = $type;

                return (object)['type' => $type];
            }
        );

        $this->assertSame([ServiceJsonConstructorProbe::class], $runtime->initializer->serviceParams);
        $this->assertSame([$json], $configurator->getServiceConfigCalls);
        $this->assertSame([
            [ServiceJsonConstructorProbe::class, 'ENABLED'],
        ], $configurator->resetCalls);
        $this->assertSame('["ZmFu"]', $json->encode(['fan']));
        $this->assertSame([], $cacheFactoryCalls);
    }

    private function jsonService(bool $useBase64 = false, ?callable $errorFactory = null): json
    {
        $service = (new ReflectionClass(json::class))->newInstanceWithoutConstructor();

        foreach ([
            'useBase64' => $useBase64,
            'config' => new ServiceJsonConfigDouble(),
            'errorFactory' => $errorFactory,
        ] as $propertyName => $value) {
            $property = new ReflectionProperty(json::class, $propertyName);
            if (!$property->isPrivate()) {
                $property = new ReflectionProperty($property->getDeclaringClass()->getName(), $propertyName);
            }
            $property->setValue($service, $value);
        }

        return $service;
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

final class ServiceJsonConstructorProbe extends json
{
    public function __construct(
        bool $useBase64,
        ?callable $errorFactory,
        ?object $serviceBootstrapRuntime,
        ?object $serviceConfigurator,
        ?callable $serviceCacheFactory
    )
    {
        parent::__construct($useBase64, $errorFactory, $serviceBootstrapRuntime, $serviceConfigurator, $serviceCacheFactory);
    }
}

final class ServiceJsonConfigDouble
{
    public function get(mixed $key = null, mixed $default = null): mixed
    {
        return $default;
    }
}

final class ServiceJsonRuntimeDouble
{
    public ServiceJsonInitializerDouble $initializer;

    public function __construct()
    {
        $this->initializer = new ServiceJsonInitializerDouble();
    }

    public function getInitializer(): ServiceJsonInitializerDouble
    {
        return $this->initializer;
    }
}

final class ServiceJsonInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class ServiceJsonConfiguratorDouble
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

final class ServiceJsonErrorDouble
{
    public array $messages = [];

    public function logErrorMessage(
        string $message,
        string $header = '',
        string $note = '',
        bool $isTrace = false,
        bool $duplicateByEmail = false
    ): void {
        $this->messages[] = [$message, $header, $note, $isTrace, $duplicateByEmail];
    }
}
