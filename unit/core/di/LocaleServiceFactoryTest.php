<?php

declare(strict_types=1);

use fan\core\di\locale_service_factory;
use fan\core\service\locale;
use PHPUnit\Framework\TestCase;
use fan\core\service\service_listener_state;
use fan\core\service\service_single_state;

final class LocaleServiceFactoryTest extends TestCase
{
    public function testFactoryCreatesCoreLocaleServiceWithoutDynamicOverrideFactory(): void
    {
        $this->ensureBaseHelpers();

        $entityFactory = static fn(): object => new stdClass();
        $tabFactory = static fn(): object => new stdClass();
        $sessionFactory = static fn(string $namespace, string $group): object => (object)[
            'namespace' => $namespace,
            'group' => $group,
        ];
        $requestFactory = static fn(): object => new stdClass();
        $cookieFactory = static fn(mixed $path = null, mixed $domain = null): object => (object)[
            'path' => $path,
            'domain' => $domain,
        ];
        $matcherFactory = static fn(): object => new stdClass();
        $arrayAdducer = static fn(mixed $value): array => is_array($value) ? $value : [$value];
        $classNameResolver = static fn(object $object): string => get_class($object);
        $runtime = new LocaleServiceFactoryRuntimeDouble();
        $config = new LocaleServiceFactoryConfigDouble([
            'AVAILABLE_LANGUAGE' => ['en' => 'English', 'fr' => 'French'],
            'DEFAULT_LANGUAGE' => 'en',
            'CHARACTER_SET' => 'utf-8',
        ]);
        $configurator = new LocaleServiceFactoryConfiguratorDouble($config);
        $cacheFactoryCalls = [];
        $cacheFactory = static function (string $type) use (&$cacheFactoryCalls): object {
            $cacheFactoryCalls[] = $type;

            return (object)['type' => $type];
        };
        $overrideCalls = [];

        $locale = (new locale_service_factory(
            static function (string $className, array $arguments) use (&$overrideCalls): object {
                $overrideCalls[] = [$className, $arguments];

                return new stdClass();
            }
        ))(
            locale::class,
            true,
            $entityFactory,
            $tabFactory,
            $sessionFactory,
            $requestFactory,
            $cookieFactory,
            $matcherFactory,
            $runtime,
            $configurator,
            $cacheFactory,
            $arrayAdducer,
            $classNameResolver
        );

        $this->assertInstanceOf(locale::class, $locale);
        $this->assertSame([], $overrideCalls);
        $this->assertSame([locale::class], $runtime->initializer->serviceParams);
        $this->assertSame([$locale], $configurator->getServiceConfigCalls);
        $this->assertSame([
            [locale::class, 'ENABLED'],
        ], $configurator->resetCalls);
        $this->assertSame(['en' => 'English', 'fr' => 'French'], $locale->getAvailableLanguages());
        $this->assertSame('en', $locale->getDefaultLanguage());
        $this->assertSame([], $cacheFactoryCalls);
    }

    public function testFactoryDelegatesConfiguredOverrideServiceCreation(): void
    {
        $entityFactory = static fn(): object => new stdClass();
        $tabFactory = static fn(): object => new stdClass();
        $sessionFactory = static fn(string $namespace, string $group): object => (object)[
            'namespace' => $namespace,
            'group' => $group,
        ];
        $requestFactory = static fn(): object => new stdClass();
        $cookieFactory = static fn(mixed $path = null, mixed $domain = null): object => (object)[
            'path' => $path,
            'domain' => $domain,
        ];
        $matcherFactory = static fn(): object => new stdClass();
        $arrayAdducer = static fn(mixed $value): array => is_array($value) ? $value : [$value];
        $classNameResolver = static fn(object $object): string => get_class($object);
        $runtime = new stdClass();
        $configurator = new stdClass();
        $cacheFactory = static fn(string $type): object => (object)['type' => $type];
        $configuredCalls = [];

        $locale = (new locale_service_factory(
            static function (string $className, array $arguments) use (&$configuredCalls): object {
                $configuredCalls[] = [$className, $arguments];

                return new $className(...$arguments);
            }
        ))(
            LocaleServiceFactoryProbe::class,
            true,
            $entityFactory,
            $tabFactory,
            $sessionFactory,
            $requestFactory,
            $cookieFactory,
            $matcherFactory,
            $runtime,
            $configurator,
            $cacheFactory,
            $arrayAdducer,
            $classNameResolver
        );

        $this->assertInstanceOf(LocaleServiceFactoryProbe::class, $locale);
        $this->assertTrue($locale->allowIni);
        $this->assertSame($entityFactory, $locale->entityFactory);
        $this->assertSame($tabFactory, $locale->tabFactory);
        $this->assertSame($sessionFactory, $locale->sessionFactory);
        $this->assertSame($requestFactory, $locale->requestFactory);
        $this->assertSame($cookieFactory, $locale->cookieFactory);
        $this->assertSame($matcherFactory, $locale->matcherFactory);
        $this->assertSame($runtime, $locale->serviceBootstrapRuntime);
        $this->assertSame($configurator, $locale->serviceConfigurator);
        $this->assertSame($cacheFactory, $locale->serviceCacheFactory);
        $this->assertSame($arrayAdducer, $locale->arrayAdducer);
        $this->assertSame($classNameResolver, $locale->classNameResolver);
        $this->assertSame(LocaleServiceFactoryProbe::class, $configuredCalls[0][0] ?? null);
        $this->assertSame([
            true,
            $entityFactory,
            $tabFactory,
            $sessionFactory,
            $requestFactory,
            $cookieFactory,
            $matcherFactory,
            $runtime,
            $configurator,
            $cacheFactory,
            $arrayAdducer,
            $classNameResolver,
        ], $configuredCalls[0][1] ?? null);
    }

                private function ensureBaseHelpers(): void
    {
        if (!function_exists('adduceToArray')) {
            eval('function adduceToArray(mixed $src): array { if (is_array($src)) { return $src; } if (is_object($src) && method_exists($src, "toArray")) { return $src->toArray(); } if ($src === null || $src === false || $src === "") { return []; } return [$src]; }');
        }
        if (!function_exists('get_class_name')) {
            eval('function get_class_name(string|object $object): ?string { if (is_object($object)) { $object = get_class($object); } $parts = explode(chr(92), $object); return end($parts); }');
        }
        if (!function_exists('fan\core\base\get_class_name')) {
            eval('namespace fan\core\base { function get_class_name(string|object $object): ?string { return \get_class_name($object); } }');
        }
    }
}

final class LocaleServiceFactoryProbe
{
    public function __construct(
        public bool $allowIni,
        public $entityFactory,
        public $tabFactory,
        public $sessionFactory,
        public $requestFactory,
        public $cookieFactory,
        public $matcherFactory,
        public object $serviceBootstrapRuntime,
        public object $serviceConfigurator,
        public $serviceCacheFactory,
        public $arrayAdducer,
        public $classNameResolver
    ) {
    }
}


final class LocaleServiceFactoryRuntimeDouble
{
    public LocaleServiceFactoryInitializerDouble $initializer;

    public function __construct()
    {
        $this->initializer = new LocaleServiceFactoryInitializerDouble();
    }

    public function getInitializer(): LocaleServiceFactoryInitializerDouble
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
}

final class LocaleServiceFactoryInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class LocaleServiceFactoryConfiguratorDouble
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

final class LocaleServiceFactoryConfigDouble extends ArrayObject
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

        return $this->offsetExists($key) ? $this[$key] : $default;
    }
}
