<?php

declare(strict_types=1);

use fan\core\di\configured_class_instantiator;
use PHPUnit\Framework\TestCase;

final class ConfiguredClassInstantiatorTest extends TestCase
{
    public function testInstantiatorConstructsConfiguredClassWithArguments(): void
    {
        $instantiator = new configured_class_instantiator(
            $reflectionClassFactory = new ConfiguredClassInstantiatorReflectionClassFactoryDouble()
        );

        $service = $instantiator(ConfiguredClassInstantiatorProbe::class, ['json', true]);

        $this->assertInstanceOf(ConfiguredClassInstantiatorProbe::class, $service);
        $this->assertSame([ConfiguredClassInstantiatorProbe::class], $reflectionClassFactory->classes);
        $this->assertSame('json', $service->name);
        $this->assertTrue($service->enabled);
    }

    public function testInstantiatorRequiresReflectionClassFactory(): void
    {
        $instantiator = new configured_class_instantiator();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Reflection class factory is not configured for configured class instantiator.');

        $instantiator(ConfiguredClassInstantiatorProbe::class, ['json', true]);
    }

    public function testInstantiatorRejectsReflectionFactoryWithoutCreateMethod(): void
    {
        $instantiator = new configured_class_instantiator(new stdClass());

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Reflection class factory must expose create().');

        $instantiator(ConfiguredClassInstantiatorProbe::class, ['json', true]);
    }

    public function testInstantiatorRejectsInvalidReflectionFactoryReturn(): void
    {
        $instantiator = new configured_class_instantiator(new ConfiguredClassInstantiatorInvalidReflectionClassFactoryDouble());

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Reflection class factory must return a ReflectionClass.');

        $instantiator(ConfiguredClassInstantiatorProbe::class, ['json', true]);
    }}

final class ConfiguredClassInstantiatorReflectionClassFactoryDouble
{
    public array $classes = [];

    public function create(object|string $object): ReflectionClass
    {
        $this->classes[] = is_object($object) ? get_class($object) : $object;

        return new ReflectionClass($object);
    }
}

final class ConfiguredClassInstantiatorInvalidReflectionClassFactoryDouble
{
    public function create(object|string $object): object
    {
        return new stdClass();
    }
}

final class ConfiguredClassInstantiatorProbe
{
    public function __construct(public string $name, public bool $enabled)
    {
    }
}
