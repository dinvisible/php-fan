<?php

declare(strict_types=1);

use fan\core\service\block_context;
use FanTest\core\SourceFileContractTestCase;


final class ServiceBlockContextTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/service/block_context.php';

    public function testReturnsEmptyContextBeforeProjectTabClassIsLoaded(): void
    {
        $context = new block_context(
            static fn(): object => throw new \LogicException('The tab service should not be requested.'),
            static fn(): object => throw new \LogicException('The bootstrap runtime service should not be requested.'),
            new ServiceBlockContextReflectionClassFactoryDouble(),
            static fn(string $className): bool => false
        );

        $this->assertSame([null, null], $context->getCurrentBlockInfo());
    }

    public function testProjectTabAvailabilityCheckIsInjected(): void
    {
        $checkedClasses = [];
        $context = new block_context(
            static fn(): object => throw new \LogicException('The tab service should not be requested.'),
            static fn(): object => throw new \LogicException('The bootstrap runtime service should not be requested.'),
            new ServiceBlockContextReflectionClassFactoryDouble(),
            static function (string $className) use (&$checkedClasses): bool {
                $checkedClasses[] = $className;

                return false;
            }
        );

        $this->assertSame([null, null], $context->getCurrentBlockInfo());
        $this->assertSame(['\fan\project\service\tab'], $checkedClasses);
    }

    public function testCurrentBlockPathUsesInjectedReflectionClassFactory(): void
    {
        $this->ensureProjectTabClassLoaded();

        $block = new ServiceBlockContextBlockDouble();
        $tab = new ServiceBlockContextTabDouble($block, 'main');
        $loader = new ServiceBlockContextLoaderDouble('/virtual/project');
        $factory = new ServiceBlockContextReflectionClassFactoryDouble();
        $context = new block_context(
            static fn(): object => $tab,
            static fn(): object => new ServiceBlockContextRuntimeDouble($loader),
            $factory
        );

        $this->assertSame(['main', '{PROJECT}/unit/core/service/BlockContextTest.php'], $context->getCurrentBlockInfo());
        $this->assertSame([$block], $factory->calls);
    }

    public function testCurrentBlockPathRequiresReflectionClassFactoryCreateMethod(): void
    {
        $this->ensureProjectTabClassLoaded();

        $context = new block_context(
            static fn(): object => new ServiceBlockContextTabDouble(new ServiceBlockContextBlockDouble(), 'main'),
            static fn(): object => new ServiceBlockContextRuntimeDouble(new ServiceBlockContextLoaderDouble('/virtual/project')),
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
        $this->assertStringContainsString('private \Closure $tabFactory;', $source);
        $this->assertStringContainsString('private \Closure $bootstrapRuntimeFactory;', $source);
        $this->assertStringContainsString('private \Closure $projectTabClassExists;', $source);
        $this->assertStringContainsString('private function tab(): object', $source);
        $this->assertStringContainsString('private function bootstrapRuntime(): object', $source);
        $this->assertStringContainsString('private function reflectionClass(object|string $object): \ReflectionClass', $source);
        $this->assertStringContainsString('private function projectTabClassExists(string $className): bool', $source);
        $this->assertStringContainsString('$this->reflectionClassFactory->create($object)', $source);
        $this->assertStringContainsString('method_exists($this->reflectionClassFactory, \'create\')', $source);
        $this->assertStringNotContainsString('container_interface', $source);
        $this->assertStringNotContainsString('$this->container->get', $source);
        $this->assertStringNotContainsString('private ?\Closure $reflectionClassFactory', $source);
        $this->assertStringNotContainsString('$this->reflectionClassFactory !== null', $source);
        $this->assertStringNotContainsString('class_exists(\'\fan\project\service\tab\', false)', $source);
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
        return $this->project . '/unit/core/service/BlockContextTest.php';
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
