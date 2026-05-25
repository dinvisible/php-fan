<?php

declare(strict_types=1);

use fan\core\base\service;
use fan\core\service\matcher\stack;
use FanTest\_core\SourceFileContractTestCase;

class ServiceMatcherStackTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/service/matcher/stack.php';

    public function testNewStackStartsWithNoLastItemAndCurrentIndexZero(): void
    {
        $stack = new stack();

        $this->assertSame(-1, $stack->getLastIndex());
        $this->assertSame(0, $stack->getCurrentIndex());
        $this->assertCount(0, $stack);
    }

    public function testLastIndexReflectsArrayIteratorCount(): void
    {
        $stack = new stack();
        $stack[] = 'first';
        $stack[] = 'second';

        $this->assertSame(1, $stack->getLastIndex());
        $this->assertSame(['first', 'second'], iterator_to_array($stack));
    }

    public function testSetFacadeStoresFacadeAndReturnsStack(): void
    {
        $stack = new stack();
        $facade = new ServiceMatcherStackFacadeDouble();

        $this->assertSame($stack, $stack->setFacade($facade));

        $reflection = new ReflectionProperty($stack, 'facade');
        $this->assertSame($facade, $reflection->getValue($stack));
    }

    public function testItemDependenciesAreStoredForNewItems(): void
    {
        $stack = new stack();
        $input = new stdClass();
        $runtime = new ServiceMatcherStackRuntimeDouble();
        $locale = new stdClass();
        $application = new stdClass();
        $routeFileStorage = new stdClass();
        $itemFactory = static fn(): object => new stdClass();
        $itemComponentFactory = static fn(): object => new stdClass();
        $serviceExceptionFactory = static fn(): Throwable => new RuntimeException('service fatal');

        $this->assertSame($stack, $stack->setItemDependencies($input, $runtime, $locale, $application, $routeFileStorage, $itemFactory, $itemComponentFactory, $serviceExceptionFactory));

        foreach ([
            'input' => $input,
            'runtime' => $runtime,
            'locale' => $locale,
            'application' => $application,
            'routeFileStorage' => $routeFileStorage,
            'itemFactory' => $itemFactory,
            'itemComponentFactory' => $itemComponentFactory,
            'serviceExceptionFactory' => $serviceExceptionFactory,
        ] as $propertyName => $value) {
            $property = new ReflectionProperty(stack::class, $propertyName);
            $this->assertSame($value, $property->getValue($stack));
        }
    }

    public function testSetNewItemUsesInjectedItemFactory(): void
    {
        $stack = new stack();
        $facade = new ServiceMatcherStackFacadeDouble();
        $input = new stdClass();
        $runtime = new ServiceMatcherStackRuntimeDouble();
        $locale = new stdClass();
        $application = new stdClass();
        $routeFileStorage = new stdClass();
        $itemComponentFactory = static fn(): object => new stdClass();
        $serviceExceptionFactory = static fn(): Throwable => new RuntimeException('service fatal');
        $item = new ServiceMatcherStackItemDouble();
        $factoryCalls = [];

        $stack->setFacade($facade);
        $stack->setItemDependencies(
            $input,
            $runtime,
            $locale,
            $application,
            $routeFileStorage,
            static function (
                int $index,
                ?object $input,
                ?object $runtime,
                ?object $locale,
                ?object $application,
                ?object $routeFileStorage,
                ?callable $componentFactory,
                ?callable $serviceExceptionFactory
            ) use (&$factoryCalls, $item): object {
                $factoryCalls[] = [$index, $input, $runtime, $locale, $application, $routeFileStorage, $componentFactory, $serviceExceptionFactory];

                return $item;
            },
            $itemComponentFactory,
            $serviceExceptionFactory
        );

        $this->assertSame($stack, $stack->setNewItem('/docs', 'example.test'));

        $this->assertSame([[0, $input, $runtime, $locale, $application, $routeFileStorage, $itemComponentFactory, $serviceExceptionFactory]], $factoryCalls);
        $this->assertSame($item, $stack[0]);
        $this->assertSame($facade, $item->facade);
        $this->assertSame([['/docs', 'example.test']], $item->outCalls);
        $this->assertSame(1, $item->preParseCalls);
    }

    public function testSourceNoLongerConstructsMatcherItemDirectly(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('$itemFactory', $source);
        $this->assertStringNotContainsString('new \fan\project\service\matcher\item', $source);
    }
}

final class ServiceMatcherStackFacadeDouble extends service
{
    public function __construct()
    {
    }

    public function isSingleton(): bool
    {
        return true;
    }
}

final class ServiceMatcherStackRuntimeDouble
{
    public function isCli(): bool
    {
        return false;
    }
}

final class ServiceMatcherStackItemDouble
{
    public ?object $facade = null;
    public array $outCalls = [];
    public int $preParseCalls = 0;

    public function setFacade(object $facade): static
    {
        $this->facade = $facade;

        return $this;
    }

    public function initOut(string $request, string $host): void
    {
        $this->outCalls[] = [$request, $host];
    }

    public function initCli(string $file, string $path): void
    {
    }

    public function preParseRequest(): static
    {
        $this->preParseCalls++;

        return $this;
    }
}
