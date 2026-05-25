<?php

declare(strict_types=1);

use fan\core\adapter\reflection_class_factory;
use PHPUnit\Framework\TestCase;

final class ReflectionClassFactoryTest extends TestCase
{
    public function testCreatesReflectionClassForObjectAndClassName(): void
    {
        $factory = new reflection_class_factory();
        $object = new ReflectionClassFactoryTargetDouble();

        $this->assertSame(ReflectionClassFactoryTargetDouble::class, $factory->create($object)->getName());
        $this->assertSame(ReflectionClassFactoryTargetDouble::class, $factory->create(ReflectionClassFactoryTargetDouble::class)->getName());
    }

    public function testSourceKeepsReflectionConstructionInsideAdapter(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/adapter/reflection_class_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class reflection_class_factory', $source);
        $this->assertStringContainsString('public function create(object|string $object): \ReflectionClass', $source);
        $this->assertStringContainsString('return new \ReflectionClass($object);', $source);
    }
}

final class ReflectionClassFactoryTargetDouble
{
}
