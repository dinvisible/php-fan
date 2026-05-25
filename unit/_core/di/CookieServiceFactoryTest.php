<?php

declare(strict_types=1);

use fan\core\di\cookie_service_factory;
use fan\core\service\cookie;
use fan\core\service\cookie_state;
use PHPUnit\Framework\TestCase;

final class CookieServiceFactoryTest extends TestCase
{
    public function testFactoryCreatesCoreCookieServiceWithoutDynamicOverrideFactory(): void
    {
        $this->ensureBaseFunctionAliases();

        $requestInput = new CookieServiceFactoryInputDouble(['prefs' => 'raw-cookie'], ['HTTPS' => 'on']);
        $errorFactory = static fn(): object => new stdClass();
        $cookieValueEncoder = static fn(mixed $value): string => json_encode($value, JSON_THROW_ON_ERROR);
        $cookieValueDecoder = static fn(string $value, mixed $default = null): mixed => 'decoded:' . $value;
        $cookieValueChecker = static fn(string $value): bool => false;
        $cookieWriter = new CookieServiceFactoryWriterDouble();
        $state = new cookie_state();
        $runtime = new CookieServiceFactoryRuntimeDouble();
        $config = new CookieServiceFactoryConfigDouble();
        $configurator = new CookieServiceFactoryConfiguratorDouble($config);
        $cacheFactoryCalls = [];
        $cacheFactory = static function (string $type) use (&$cacheFactoryCalls): object {
            $cacheFactoryCalls[] = $type;

            return (object)['type' => $type];
        };
        $overrideCalls = [];

        $cookie = (new cookie_service_factory(
            static function (string $className, array $arguments) use (&$overrideCalls): object {
                $overrideCalls[] = [$className, $arguments];

                return new stdClass();
            }
        ))(
            cookie::class,
            '/admin',
            'example.test',
            true,
            $requestInput,
            $errorFactory,
            $cookieValueEncoder,
            $cookieValueDecoder,
            $cookieValueChecker,
            $cookieWriter,
            $state,
            $runtime,
            $configurator,
            $cacheFactory
        );

        $this->assertInstanceOf(cookie::class, $cookie);
        $this->assertSame([], $overrideCalls);
        $this->assertSame([], $runtime->initializer->serviceParams);
        $this->assertSame([$cookie], $configurator->getServiceConfigCalls);
        $this->assertContains($configurator->resetCalls[0][0] ?? null, ['cookie', cookie::class]);
        $this->assertSame('ENABLED', $configurator->resetCalls[0][1] ?? null);
        $this->assertSame('raw-cookie', $state->getData('prefs'));
        $this->assertSame('raw-cookie', $cookie->get('prefs'));
        $this->assertSame([], $cookieWriter->writes);
        $this->assertSame([], $cacheFactoryCalls);
    }

    public function testFactoryDelegatesConfiguredOverrideServiceCreation(): void
    {
        $requestInput = new stdClass();
        $errorFactory = static fn(): object => new stdClass();
        $cookieValueEncoder = static fn(mixed $value): string => json_encode($value, JSON_THROW_ON_ERROR);
        $cookieValueDecoder = static fn(string $value, mixed $default = null): mixed => $default;
        $cookieValueChecker = static fn(string $value): bool => true;
        $cookieWriter = new stdClass();
        $state = new stdClass();
        $runtime = new stdClass();
        $configurator = new stdClass();
        $cacheFactory = static fn(string $type): object => (object)['type' => $type];
        $configuredCalls = [];

        $cookie = (new cookie_service_factory(
            static function (string $className, array $arguments) use (&$configuredCalls): object {
                $configuredCalls[] = [$className, $arguments];

                return new $className(...$arguments);
            }
        ))(
            CookieServiceFactoryProbe::class,
            '/admin',
            'example.test',
            true,
            $requestInput,
            $errorFactory,
            $cookieValueEncoder,
            $cookieValueDecoder,
            $cookieValueChecker,
            $cookieWriter,
            $state,
            $runtime,
            $configurator,
            $cacheFactory
        );

        $this->assertInstanceOf(CookieServiceFactoryProbe::class, $cookie);
        $this->assertSame('/admin', $cookie->path);
        $this->assertSame('example.test', $cookie->domain);
        $this->assertTrue($cookie->secure);
        $this->assertSame($requestInput, $cookie->requestInput);
        $this->assertSame($errorFactory, $cookie->errorFactory);
        $this->assertSame($cookieValueEncoder, $cookie->cookieValueEncoder);
        $this->assertSame($cookieValueDecoder, $cookie->cookieValueDecoder);
        $this->assertSame($cookieValueChecker, $cookie->cookieValueChecker);
        $this->assertSame($cookieWriter, $cookie->cookieWriter);
        $this->assertSame($state, $cookie->state);
        $this->assertSame($runtime, $cookie->serviceBootstrapRuntime);
        $this->assertSame($configurator, $cookie->serviceConfigurator);
        $this->assertSame($cacheFactory, $cookie->serviceCacheFactory);
        $this->assertSame(CookieServiceFactoryProbe::class, $configuredCalls[0][0] ?? null);
        $this->assertSame([
            '/admin',
            'example.test',
            true,
            $requestInput,
            $errorFactory,
            $cookieValueEncoder,
            $cookieValueDecoder,
            $cookieValueChecker,
            $cookieWriter,
            $state,
            $runtime,
            $configurator,
            $cacheFactory,
        ], $configuredCalls[0][1] ?? null);
    }

                private function ensureBaseFunctionAliases(): void
    {
        if (!function_exists('get_class_name')) {
            eval('function get_class_name(string|object $object): ?string { if (is_object($object)) { $object = get_class($object); } $parts = explode(chr(92), $object); return end($parts); }');
        }
        if (!function_exists('fan\core\base\get_class_name')) {
            eval('namespace fan\core\base { function get_class_name(string|object $object): ?string { return \get_class_name($object); } }');
        }
    }
}

final class CookieServiceFactoryProbe
{
    public function __construct(
        public mixed $path,
        public mixed $domain,
        public bool $secure,
        public object $requestInput,
        public $errorFactory,
        public $cookieValueEncoder,
        public $cookieValueDecoder,
        public $cookieValueChecker,
        public object $cookieWriter,
        public object $state,
        public object $serviceBootstrapRuntime,
        public object $serviceConfigurator,
        public $serviceCacheFactory
    ) {
    }
}


final class CookieServiceFactoryInputDouble
{
    public function __construct(private array $globals = [], private array $server = [])
    {
    }

    public function globalArray(string $name): array
    {
        return $name === '_COOKIE' ? $this->globals : [];
    }

    public function serverValue(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }
}

final class CookieServiceFactoryWriterDouble
{
    public array $writes = [];

    public function write(
        string $name,
        string $value,
        int $expires,
        string $path,
        string $domain,
        bool $secure,
        bool $httpOnly
    ): bool {
        $this->writes[] = [$name, $value, $expires, $path, $domain, $secure, $httpOnly];

        return true;
    }
}

final class CookieServiceFactoryRuntimeDouble
{
    public CookieServiceFactoryInitializerDouble $initializer;

    public function __construct()
    {
        $this->initializer = new CookieServiceFactoryInitializerDouble();
    }

    public function getInitializer(): CookieServiceFactoryInitializerDouble
    {
        return $this->initializer;
    }

    public function classNameResolver(): callable
    {
        return static fn(object $object): string => get_class($object);
    }
}

final class CookieServiceFactoryInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class CookieServiceFactoryConfiguratorDouble
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

    public function reset(string $className, mixed $key): void
    {
        $this->resetCalls[] = [$className, $key];
    }
}

final class CookieServiceFactoryConfigDouble
{
    public function get(mixed $key = null, mixed $default = null): mixed
    {
        return $default;
    }
}
