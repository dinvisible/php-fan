<?php

declare(strict_types=1);

use fan\core\di\bootstrap_object_defaults_factory;
use fan\core\bootstrap\bootstrap_object_factory;
use PHPUnit\Framework\TestCase;
use fan\core\adapter\reflection_class_factory;
use fan\core\di\configured_class_instantiator;
use fan\core\di\configured_service_factory;


final class BootstrapObjectDefaultsFactoryTest extends TestCase
{
    public function testFactoryCreatesBootstrapObjectFactory(): void
    {
        $defaultsFactory = new bootstrap_object_defaults_factory(
            static fn(): callable => new bootstrap_object_factory(
                new configured_service_factory(
                    new configured_class_instantiator(
                        new reflection_class_factory()
                    )
                )
            )
        );
        $factory = $defaultsFactory->objectFactory();

        $this->assertInstanceOf(bootstrap_object_factory::class, $factory);

        $object = $factory(BootstrapObjectDefaultsFactoryProbe::class, ['config.php']);

        $this->assertInstanceOf(BootstrapObjectDefaultsFactoryProbe::class, $object);
        $this->assertSame('config.php', $object->configPath);
    }

    public function testSourceOwnsBootstrapObjectDefaultAssembly(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/factory/bootstrap_object_defaults_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class bootstrap_object_defaults_factory', $source);
        $this->assertStringContainsString('private \Closure $objectFactoryFactory;', $source);
        $this->assertStringContainsString('public function __construct(callable $objectFactoryFactory)', $source);
        $this->assertStringContainsString('$this->objectFactoryFactory = \Closure::fromCallable($objectFactoryFactory);', $source);
        $this->assertStringContainsString('public function objectFactory(): callable', $source);
        $this->assertStringContainsString('return ($this->objectFactoryFactory)();', $source);
        $this->assertStringNotContainsString('public static function objectFactory(): callable', $source);
        $this->assertStringNotContainsString('loadClass', $source);
        $this->assertStringNotContainsString('require_once', $source);
        $this->assertStringNotContainsString('new \fan\core\di\configured_service_factory(', $source);
        $this->assertStringNotContainsString('new \fan\core\di\configured_class_instantiator()', $source);
        $this->assertStringNotContainsString('new bootstrap_object_factory(', $source);
    }
}

final class BootstrapObjectDefaultsFactoryProbe
{
    public function __construct(
        public string $configPath
    ) {
    }
}
