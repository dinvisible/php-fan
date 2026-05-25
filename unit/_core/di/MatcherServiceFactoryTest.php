<?php

declare(strict_types=1);

use fan\core\di\matcher_service_factory;
use fan\core\service\matcher;
use fan\core\service\matcher\stack;
use PHPUnit\Framework\TestCase;
use fan\core\service\service_listener_state;
use fan\core\service\service_single_state;


if (!function_exists('get_class_name')) {
    function get_class_name(string|object $object): ?string
    {
        if (is_object($object)) {
            $object = get_class($object);
        }

        $parts = explode('\\', $object);

        return end($parts);
    }
}

final class MatcherServiceFactoryTest extends TestCase
{
    public function testFactoryCreatesCoreMatcherServiceWithTypedConstructor(): void
    {
        $overrideCalls = [];
        $factory = new matcher_service_factory(
            static function (string $className, array $arguments) use (&$overrideCalls): object {
                $overrideCalls[] = [$className, $arguments];

                return new stdClass();
            }
        );
        $stack = new MatcherServiceFactoryStackDouble();
        $runtime = new MatcherServiceFactoryRuntimeDouble($stack);
        $input = new stdClass();
        $locale = new stdClass();
        $application = new stdClass();
        $routeFileStorage = new stdClass();
        $itemFactory = static fn(): object => new stdClass();
        $itemComponentFactory = static fn(): object => new stdClass();
        $configurator = new MatcherServiceFactoryConfiguratorDouble(new MatcherServiceFactoryConfigDouble());
        $cacheFactory = static fn(string $type): object => (object)['type' => $type];
        $serviceExceptionFactory = $runtime->serviceExceptionFactory();

        $matcher = $factory(
            matcher::class,
            true,
            $input,
            $runtime,
            $locale,
            $application,
            $routeFileStorage,
            $itemFactory,
            $itemComponentFactory,
            $runtime,
            $configurator,
            $cacheFactory
        );

        $this->assertInstanceOf(matcher::class, $matcher);
        $this->assertSame([], $overrideCalls);
        $this->assertSame([matcher::class], $runtime->initializer->serviceParams);
        $this->assertSame([[$input, $runtime, $locale, $application, $routeFileStorage, $itemFactory, $itemComponentFactory, $serviceExceptionFactory]], $stack->dependencyCalls);
        $this->assertSame([$matcher], $configurator->getServiceConfigCalls);
        $this->assertSame([['matcher', 'ENABLED']], $configurator->resetCalls);
        $this->assertSame($stack, $matcher->getStack());
    }

    public function testFactoryDelegatesConfiguredMatcherServiceOverrides(): void
    {
        $calls = [];
        $factory = new matcher_service_factory(
            static function (string $className, array $arguments) use (&$calls): object {
                $calls[] = [$className, $arguments];

                return new MatcherServiceFactoryProbe(...$arguments);
            }
        );
        $input = new stdClass();
        $runtime = new stdClass();
        $locale = new stdClass();
        $application = new stdClass();
        $routeFileStorage = new stdClass();
        $itemFactory = static fn(): object => new stdClass();
        $itemComponentFactory = static fn(): object => new stdClass();
        $configurator = new stdClass();
        $cacheFactory = static fn(string $type): object => (object)['type' => $type];

        $matcher = $factory(
            MatcherServiceFactoryProbe::class,
            true,
            $input,
            $runtime,
            $locale,
            $application,
            $routeFileStorage,
            $itemFactory,
            $itemComponentFactory,
            $runtime,
            $configurator,
            $cacheFactory
        );

        $this->assertInstanceOf(MatcherServiceFactoryProbe::class, $matcher);
        $this->assertSame(MatcherServiceFactoryProbe::class, $calls[0][0]);
        $this->assertSame([
            true,
            $input,
            $runtime,
            $locale,
            $application,
            $routeFileStorage,
            $itemFactory,
            $itemComponentFactory,
            $runtime,
            $configurator,
            $cacheFactory,
        ], $calls[0][1]);
        $this->assertTrue($matcher->allowIni);
        $this->assertSame($input, $matcher->input);
        $this->assertSame($runtime, $matcher->runtime);
        $this->assertSame($locale, $matcher->locale);
        $this->assertSame($application, $matcher->application);
        $this->assertSame($routeFileStorage, $matcher->routeFileStorage);
        $this->assertSame($itemFactory, $matcher->itemFactory);
        $this->assertSame($itemComponentFactory, $matcher->itemComponentFactory);
        $this->assertSame($runtime, $matcher->serviceBootstrapRuntime);
        $this->assertSame($configurator, $matcher->serviceConfigurator);
        $this->assertSame($cacheFactory, $matcher->serviceCacheFactory);
    }

            }

final class MatcherServiceFactoryProbe
{
    public function __construct(
        public bool $allowIni,
        public object $input,
        public object $runtime,
        public object $locale,
        public object $application,
        public object $routeFileStorage,
        public $itemFactory,
        public $itemComponentFactory,
        public object $serviceBootstrapRuntime,
        public object $serviceConfigurator,
        public $serviceCacheFactory
    ) {
    }
}

final class MatcherServiceFactoryRuntimeDouble
{
    public MatcherServiceFactoryInitializerDouble $initializer;
    public array $loadClassCalls = [];
    private $serviceExceptionFactory;

    public function __construct(private object $stack)
    {
        $this->initializer = new MatcherServiceFactoryInitializerDouble();
        $this->serviceExceptionFactory = static fn(): Throwable => new RuntimeException('service fatal');
    }

    public function getInitializer(): MatcherServiceFactoryInitializerDouble
    {
        return $this->initializer;
    }

    public function loadClass(string $className, bool $throw = false): bool
    {
        $this->loadClassCalls[] = [$className, $throw];

        return true;
    }

    public function serviceEngineFactory(): callable
    {
        return fn(string $className): object => $this->stack;
    }

    public function serviceExceptionFactory(): callable
    {
        return $this->serviceExceptionFactory;
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
        return static fn(object|string $object): ?string => get_class_name($object);
    }
}

final class MatcherServiceFactoryInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class MatcherServiceFactoryConfiguratorDouble
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

final class MatcherServiceFactoryConfigDouble
{
    public function get(mixed $key = null, mixed $default = null): mixed
    {
        return $default;
    }
}

final class MatcherServiceFactoryStackDouble extends stack
{
    public array $dependencyCalls = [];

    public function setItemDependencies(
        ?object $input = null,
        ?object $runtime = null,
        ?object $locale = null,
        ?object $application = null,
        ?object $routeFileStorage = null,
        ?callable $itemFactory = null,
        ?callable $itemComponentFactory = null,
        ?callable $serviceExceptionFactory = null
    ): static {
        $this->dependencyCalls[] = [$input, $runtime, $locale, $application, $routeFileStorage, $itemFactory, $itemComponentFactory, $serviceExceptionFactory];

        return parent::setItemDependencies($input, $runtime, $locale, $application, $routeFileStorage, $itemFactory, $itemComponentFactory, $serviceExceptionFactory);
    }
}


