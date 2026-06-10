<?php

declare(strict_types=1);

use fan\core\bootstrap\bootstrap_object_factory;
use PHPUnit\Framework\TestCase;

final class BootstrapObjectFactoryTest extends TestCase
{
    public function testFactoryCreatesBootstrapObjectWithArguments(): void
    {
        $delegatedClass = null;
        $delegatedArguments = null;
        $factory = new bootstrap_object_factory(
            static function (string $className, array $arguments) use (&$delegatedClass, &$delegatedArguments): object {
                $delegatedClass = $className;
                $delegatedArguments = $arguments;

                return new $className(...$arguments);
            }
        );

        $object = $factory(BootstrapObjectFactoryProbe::class, ['config.php', 7]);

        $this->assertSame(BootstrapObjectFactoryProbe::class, $delegatedClass);
        $this->assertSame(['config.php', 7], $delegatedArguments);
        $this->assertInstanceOf(BootstrapObjectFactoryProbe::class, $object);
        $this->assertSame('config.php', $object->configPath);
        $this->assertSame(7, $object->priority);
    }

    public function testSourceDelegatesDynamicBootstrapObjectConstruction(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/factory/bootstrap_object_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class bootstrap_object_factory', $source);
        $this->assertStringContainsString('public function __construct(callable $configuredServiceFactory)', $source);
        $this->assertStringContainsString('$object = ($this->configuredServiceFactory)($class, $arguments);', $source);
        $this->assertStringNotContainsString('new $class(', $source);
        $this->assertStringNotContainsString('defaultConfiguredServiceFactory', $source);
        $this->assertStringNotContainsString('configured_service_factory.php', $source);
    }
}

final class BootstrapObjectFactoryProbe
{
    public function __construct(
        public string $configPath,
        public int $priority
    ) {
    }
}
