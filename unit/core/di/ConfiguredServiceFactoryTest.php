<?php

declare(strict_types=1);

use fan\core\di\configured_service_factory;
use PHPUnit\Framework\TestCase;

final class ConfiguredServiceFactoryTest extends TestCase
{
    public function testFactoryConstructsConfiguredClassWithArguments(): void
    {
        $factory = new configured_service_factory(
            static fn(string $className, array $arguments): object => new $className(...$arguments)
        );

        $service = $factory(ConfiguredServiceFactoryProbe::class, ['json', true]);

        $this->assertInstanceOf(ConfiguredServiceFactoryProbe::class, $service);
        $this->assertSame('json', $service->name);
        $this->assertTrue($service->enabled);
    }

    public function testFactoryUsesInjectedClassInstantiator(): void
    {
        $calls = [];
        $expected = new stdClass();
        $factory = new configured_service_factory(
            static function (string $className, array $arguments) use (&$calls, $expected): object {
                $calls[] = [$className, $arguments];

                return $expected;
            }
        );

        $this->assertSame($expected, $factory('configured-service', ['one']));
        $this->assertSame([['configured-service', ['one']]], $calls);
    }

    public function testFactoryRejectsNonObjectInstantiatorResult(): void
    {
        $factory = new configured_service_factory(static fn(): string => 'not-object');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Configured class instantiator must return an object.');

        $factory('configured-service', []);
    }
}

final class ConfiguredServiceFactoryProbe
{
    public function __construct(public string $name, public bool $enabled)
    {
    }
}
