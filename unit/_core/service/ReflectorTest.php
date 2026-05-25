<?php

declare(strict_types=1);

use fan\core\service\reflector;
use FanTest\_core\SourceFileContractTestCase;

class ServiceReflectorTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/service/reflector.php';

    public function testGetReflectionAcceptsClassNameAndObject(): void
    {
        $service = $this->reflector();
        $object = new ServiceReflectorChildDouble();

        $this->assertSame(ServiceReflectorChildDouble::class, $service->getReflection(ServiceReflectorChildDouble::class)->getName());
        $this->assertSame(ServiceReflectorChildDouble::class, $service->getReflection($object)->getName());
    }

    public function testParentChainIncludesClassAndParentsInOrder(): void
    {
        $service = $this->reflector();

        $chain = $service->getParentChain(ServiceReflectorChildDouble::class);

        $this->assertSame([
            ServiceReflectorChildDouble::class,
            ServiceReflectorParentDouble::class,
        ], array_keys($chain));
        $this->assertInstanceOf(ReflectionClass::class, $chain[ServiceReflectorChildDouble::class]);
    }

    public function testParentPathsAreKeyedByClassName(): void
    {
        $service = $this->reflector();

        $paths = $service->getParentPaths(ServiceReflectorChildDouble::class);

        $this->assertArrayHasKey(ServiceReflectorChildDouble::class, $paths);
        $this->assertSame(__FILE__, $paths[ServiceReflectorChildDouble::class]);
    }

    public function testSetReflectionUsesInjectedReflectionClassFactory(): void
    {
        $factory = new ServiceReflectorReflectionClassFactoryDouble();
        $service = $this->reflector($factory);

        $this->assertSame(ServiceReflectorChildDouble::class, $service->getReflection(ServiceReflectorChildDouble::class)->getName());
        $this->assertSame([ServiceReflectorChildDouble::class], $factory->calls);
        $this->assertSame(ServiceReflectorChildDouble::class, $service->getReflection(ServiceReflectorChildDouble::class)->getName());
        $this->assertSame([ServiceReflectorChildDouble::class], $factory->calls);
    }

    public function testSetReflectionRequiresReflectionClassFactoryCreateMethod(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Reflection class factory must expose create().');

        $this->reflector(new stdClass())->getReflection(ServiceReflectorChildDouble::class);
    }

    private function reflector(?object $reflectionClassFactory = null): reflector
    {
        $service = new ServiceReflectorProbe();
        $property = new ReflectionProperty(reflector::class, 'reflectionClassFactory');
        $property->setValue(
            $service,
            $reflectionClassFactory ?? new ServiceReflectorReflectionClassFactoryDouble()
        );

        return $service;
    }
}

final class ServiceReflectorProbe extends reflector
{
    public function __construct()
    {
    }
}

class ServiceReflectorParentDouble
{
}

final class ServiceReflectorChildDouble extends ServiceReflectorParentDouble
{
}

final class ServiceReflectorReflectionClassFactoryDouble
{
    public array $calls = [];

    public function create(object|string $object): ReflectionClass
    {
        $this->calls[] = $object;

        return new ReflectionClass($object);
    }
}
