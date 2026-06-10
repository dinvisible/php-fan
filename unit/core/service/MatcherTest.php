<?php

declare(strict_types=1);

use fan\core\service\matcher;
use fan\core\service\matcher\item;
use fan\core\service\matcher\stack;
use FanTest\core\SourceFileContractTestCase;
use fan\core\service\service_listener_state;
use fan\core\service\service_single_state;


class ServiceMatcherTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/service/matcher.php';

    public function testSetUriForwardsRequestToStackAndReturnsMatcher(): void
    {
        $stack = new ServiceMatcherStackDouble();
        $matcher = $this->matcher($stack);

        $this->assertSame($matcher, $matcher->setUri('/catalog', 'example.test', false));

        $this->assertSame([
            ['request' => '/catalog', 'position' => 'example.test', 'shiftCurrent' => false],
        ], $stack->calls);
        $this->assertSame(0, $matcher->getLastIndex());
        $this->assertSame(0, $matcher->getCurrentIndex());
    }

    public function testSetCliForwardsFileAndPathWithoutShiftingCurrentItem(): void
    {
        $stack = new ServiceMatcherStackDouble();
        $matcher = $this->matcher($stack);

        $matcher->setCli('fan.php', '/var/www');

        $this->assertSame([
            ['request' => 'fan.php', 'position' => '/var/www', 'shiftCurrent' => false],
        ], $stack->calls);
        $this->assertSame(0, $matcher->getCurrentIndex());
    }

    public function testStackAndItemAccessorsReturnInjectedObjects(): void
    {
        $stack = new ServiceMatcherStackDouble();
        $first = new ServiceMatcherItemDouble('first');
        $second = new ServiceMatcherItemDouble('second');
        $stack[] = $first;
        $stack[] = $second;
        $stack->currentIndex = 1;

        $matcher = $this->matcher($stack);

        $this->assertSame($stack, $matcher->getStack());
        $this->assertSame(1, $matcher->getLastIndex());
        $this->assertSame(1, $matcher->getCurrentIndex());
        $this->assertSame($first, $matcher->getItem(0));
        $this->assertSame($second, $matcher->getLastItem());
        $this->assertSame($second, $matcher->getCurrentItem());
    }

    public function testGetItemUsesInjectedServiceExceptionFactoryWhenItemIsMissing(): void
    {
        $stack = new ServiceMatcherStackDouble();
        $matcher = $this->matcher($stack);
        $calls = [];
        $matcher->setServiceDependencies(
            null,
            null,
            null,
            null,
            null,
            null,
            static function (string $exceptionClass, object $service, string $message, int $code, ?Throwable $previous = null) use (&$calls): Throwable {
                $calls[] = [$exceptionClass, $service, $message, $code, $previous];

                return new RuntimeException($message, $code, $previous);
            }
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Requested item number "3" isn\'t set');

        try {
            $matcher->getItem(3);
        } finally {
            $this->assertCount(1, $calls);
            $this->assertSame('\fan\project\exception\service\fatal', $calls[0][0]);
            $this->assertSame($matcher, $calls[0][1]);
            $this->assertSame('Requested item number "3" isn\'t set', $calls[0][2]);
            $this->assertSame(E_USER_ERROR, $calls[0][3]);
            $this->assertNull($calls[0][4]);
        }
    }

    public function testConstructorUsesInjectedBaseServiceDependencies(): void
    {
        $this->ensureBaseHelper();
        $stack = new ServiceMatcherStackDouble();
        $runtime = new ServiceMatcherRuntimeDouble();
        $configurator = new ServiceMatcherConfiguratorDouble(new ServiceMatcherConfigDouble());
        $cacheFactoryCalls = [];
        $input = new stdClass();
        $locale = new stdClass();
        $application = new stdClass();
        $routeFileStorage = new stdClass();
        $itemFactory = static fn(): object => new stdClass();
        $itemComponentFactory = static fn(): object => new stdClass();
        $serviceExceptionFactory = $runtime->serviceExceptionFactory();

        $matcher = new ServiceMatcherConstructorProbe(
            $stack,
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
            static function (string $type) use (&$cacheFactoryCalls): object {
                $cacheFactoryCalls[] = $type;

                return (object)['type' => $type];
            }
        );

        $this->assertSame([[$input, $runtime, $locale, $application, $routeFileStorage, $itemFactory, $itemComponentFactory, $serviceExceptionFactory]], $stack->dependencyCalls);
        $this->assertSame([ServiceMatcherConstructorProbe::class], $runtime->initializer->serviceParams);
        $this->assertSame([$matcher], $configurator->getServiceConfigCalls);
        $this->assertSame([
            [ServiceMatcherConstructorProbe::class, 'ENABLED'],
        ], $configurator->resetCalls);
        $this->assertSame([], $cacheFactoryCalls);
    }

    private function ensureBaseHelper(): void
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

    private function matcher(stack $stack): matcher
    {
        $matcher = new ServiceMatcherProbe();
        $property = new ReflectionProperty(matcher::class, 'stack');
        $property->setValue($matcher, $stack);

        return $matcher;
    }
}

final class ServiceMatcherProbe extends matcher
{
    public function __construct()
    {
    }

    protected function _broadcastMessage(string $eventName, mixed $data): void
    {
    }
}

final class ServiceMatcherConstructorProbe extends matcher
{
    public function __construct(
        private stack $stackDouble,
        bool $allowIni,
        ?object $input,
        ?object $runtime,
        ?object $locale,
        ?object $application,
        ?object $routeFileStorage,
        ?callable $itemFactory,
        ?callable $itemComponentFactory,
        ?object $serviceBootstrapRuntime,
        ?object $serviceConfigurator,
        ?callable $serviceCacheFactory
    )
    {
        parent::__construct(
            $allowIni,
            $input,
            $runtime,
            $locale,
            $application,
            $routeFileStorage,
            $itemFactory,
            $itemComponentFactory,
            $serviceBootstrapRuntime,
            $serviceConfigurator,
            $serviceCacheFactory
        );
    }

    protected function _getEngine($name, $object = true): mixed
    {
        return $name === 'stack' ? $this->stackDouble : parent::_getEngine($name, $object);
    }
}

final class ServiceMatcherStackDouble extends stack
{
    public array $calls = [];
    public array $dependencyCalls = [];
    public int $currentIndex = 0;

    public function setItemDependencies(
        ?object $input = null,
        ?object $runtime = null,
        ?object $locale = null,
        ?object $application = null,
        ?object $routeFileStorage = null,
        ?callable $itemFactory = null,
        ?callable $itemComponentFactory = null,
        ?callable $serviceExceptionFactory = null
    ): static
    {
        $this->dependencyCalls[] = [$input, $runtime, $locale, $application, $routeFileStorage, $itemFactory, $itemComponentFactory, $serviceExceptionFactory];

        return parent::setItemDependencies($input, $runtime, $locale, $application, $routeFileStorage, $itemFactory, $itemComponentFactory, $serviceExceptionFactory);
    }

    public function setNewItem(string $request, ?string $position = null, bool $shiftCurrent = true): static
    {
        $this->calls[] = [
            'request' => $request,
            'position' => $position,
            'shiftCurrent' => $shiftCurrent,
        ];

        $index = count($this);
        if ($shiftCurrent) {
            $this->currentIndex = $index;
        }
        $this[$index] = new ServiceMatcherItemDouble($request);

        return $this;
    }

    public function getCurrentIndex(): int
    {
        return $this->currentIndex;
    }
}

final class ServiceMatcherItemDouble extends item
{
    public function __construct(public string $label)
    {
    }
}

final class ServiceMatcherRuntimeDouble
{
    public ServiceMatcherInitializerDouble $initializer;
    private $serviceExceptionFactory;

    public function __construct()
    {
        $this->initializer = new ServiceMatcherInitializerDouble();
        $this->serviceExceptionFactory = static fn(): Throwable => new RuntimeException('service fatal');
    }

    public function getInitializer(): ServiceMatcherInitializerDouble
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

    public function serviceExceptionFactory(): callable
    {
        return $this->serviceExceptionFactory;
    }
}

final class ServiceMatcherInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class ServiceMatcherConfiguratorDouble
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

final class ServiceMatcherConfigDouble
{
    public function get(mixed $key = null, mixed $default = null): mixed
    {
        return $default;
    }
}
