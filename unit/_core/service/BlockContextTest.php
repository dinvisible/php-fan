<?php

declare(strict_types=1);

use fan\core\service\block_context;
use FanTest\_core\SourceFileContractTestCase;
use fan\core\di\container_interface;


final class ServiceBlockContextTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/service/block_context.php';

    public function testReturnsEmptyContextBeforeProjectTabClassIsLoaded(): void
    {
        $context = new block_context(
            new class implements container_interface {
                public function has(string $id): bool
                {
                    return false;
                }

                public function get(string $id, mixed ...$arguments): mixed
                {
                    throw new \LogicException('The tab service should not be requested.');
                }
            },
            new ServiceBlockContextReflectionClassFactoryDouble()
        );

        if (!class_exists('\fan\project\service\tab', false)) {
            $this->assertSame([null, null], $context->getCurrentBlockInfo());
        } else {
            $this->expectNotToPerformAssertions();
        }
    }

    public function testCurrentBlockPathUsesInjectedReflectionClassFactory(): void
    {
        $this->ensureProjectTabClassLoaded();

        $block = new ServiceBlockContextBlockDouble();
        $tab = new ServiceBlockContextTabDouble($block, 'main');
        $loader = new ServiceBlockContextLoaderDouble('/virtual/project');
        $container = new ServiceBlockContextContainerDouble([
            'tab' => $tab,
            'bootstrap_runtime' => new ServiceBlockContextRuntimeDouble($loader),
        ]);
        $factory = new ServiceBlockContextReflectionClassFactoryDouble();
        $context = new block_context(
            $container,
            $factory
        );

        $this->assertSame(['main', '{PROJECT}/unit/_core/service/BlockContextTest.php'], $context->getCurrentBlockInfo());
        $this->assertSame([$block], $factory->calls);
    }

    public function testCurrentBlockPathRequiresReflectionClassFactoryCreateMethod(): void
    {
        $this->ensureProjectTabClassLoaded();

        $context = new block_context(
            new ServiceBlockContextContainerDouble([
                'tab' => new ServiceBlockContextTabDouble(new ServiceBlockContextBlockDouble(), 'main'),
                'bootstrap_runtime' => new ServiceBlockContextRuntimeDouble(new ServiceBlockContextLoaderDouble('/virtual/project')),
            ]),
            new stdClass()
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Reflection class factory must expose create().');

        $context->getCurrentBlockInfo();
    }

    public function testSourceUsesInjectedReflectionClassFactory(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('private object $reflectionClassFactory', $source);
        $this->assertStringContainsString('private function reflectionClass(object|string $object): \ReflectionClass', $source);
        $this->assertStringContainsString('$this->reflectionClassFactory->create($object)', $source);
        $this->assertStringContainsString('method_exists($this->reflectionClassFactory, \'create\')', $source);
        $this->assertStringNotContainsString('\Closure::fromCallable', $source);
        $this->assertStringNotContainsString('private ?\Closure $reflectionClassFactory', $source);
        $this->assertStringNotContainsString('$this->reflectionClassFactory !== null', $source);
        $this->assertStringNotContainsString('new \ReflectionClass($block)', $source);
    }

    private function ensureProjectTabClassLoaded(): void
    {
        if (class_exists('\fan\project\service\tab', false)) {
            return;
        }

        eval('namespace fan\project\service; class tab {}');
    }
}

final class ServiceBlockContextContainerDouble implements container_interface
{
    public function __construct(private array $services)
    {
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->services);
    }

    public function get(string $id, mixed ...$arguments): mixed
    {
        return $this->services[$id] ?? throw new LogicException('Unexpected service "' . $id . '".');
    }
}

final class ServiceBlockContextTabDouble
{
    public function __construct(private ?object $block, private string $stage)
    {
    }

    public function getCurrentBlock(): ?object
    {
        return $this->block;
    }

    public function getTabStage(): string
    {
        return $this->stage;
    }
}

final class ServiceBlockContextRuntimeDouble
{
    public function __construct(private object $loader)
    {
    }

    public function getLoader(): object
    {
        return $this->loader;
    }
}

final class ServiceBlockContextLoaderDouble
{
    public function __construct(public string $project)
    {
    }

    public function getRealPath(string $path): string
    {
        return $this->project . '/unit/_core/service/BlockContextTest.php';
    }
}

final class ServiceBlockContextBlockDouble
{
}

final class ServiceBlockContextReflectionClassFactoryDouble
{
    public array $calls = [];

    public function create(object|string $object): ReflectionClass
    {
        $this->calls[] = $object;

        return new ReflectionClass($object);
    }
}
