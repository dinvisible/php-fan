<?php

declare(strict_types=1);

use fan\core\di\bootstrap_object_defaults_factory;
use fan\core\di\bootstrap_object_defaults_provider_factory;
use fan\core\bootstrap\bootstrap_object_factory;
use PHPUnit\Framework\TestCase;

final class BootstrapObjectDefaultsProviderFactoryTest extends TestCase
{
    public function testFactoryCreatesBootstrapObjectDefaultsProvider(): void
    {
        $provider = (new bootstrap_object_defaults_provider_factory())();
        $factory = $provider->objectFactory();

        $this->assertInstanceOf(bootstrap_object_defaults_factory::class, $provider);
        $this->assertInstanceOf(bootstrap_object_factory::class, $factory);

        $object = $factory(BootstrapObjectDefaultsProviderFactoryProbe::class, ['config.php']);

        $this->assertInstanceOf(BootstrapObjectDefaultsProviderFactoryProbe::class, $object);
        $this->assertSame('config.php', $object->configPath);
    }

    public function testFactoryUsesInjectedObjectCompositionFactories(): void
    {
        $receivedClassInstantiator = null;
        $receivedConfiguredServiceFactory = null;
        $provider = (new bootstrap_object_defaults_provider_factory(
            objectFactoryFactory: static function (callable $configuredServiceFactory) use (&$receivedConfiguredServiceFactory): callable {
                $receivedConfiguredServiceFactory = $configuredServiceFactory;

                return static fn(string $className, array $arguments): object => $configuredServiceFactory($className, $arguments);
            },
            configuredServiceFactoryFactory: static function (callable $classInstantiator) use (&$receivedClassInstantiator): callable {
                $receivedClassInstantiator = $classInstantiator;

                return static fn(string $className, array $arguments): object => $classInstantiator($className, $arguments);
            },
            classInstantiatorFactory: static fn(): callable => static fn(string $className, array $arguments): object => new $className('injected-' . $arguments[0])
        ))();

        $factory = $provider->objectFactory();
        $object = $factory(BootstrapObjectDefaultsProviderFactoryProbe::class, ['config.php']);

        $this->assertInstanceOf(bootstrap_object_defaults_factory::class, $provider);
        $this->assertIsCallable($receivedClassInstantiator);
        $this->assertIsCallable($receivedConfiguredServiceFactory);
        $this->assertInstanceOf(BootstrapObjectDefaultsProviderFactoryProbe::class, $object);
        $this->assertSame('injected-config.php', $object->configPath);
    }

    public function testFactoryUsesInjectedDefaultObjectProviders(): void
    {
        $objectFactoryProviderCalls = 0;
        $configuredServiceFactoryProviderCalls = 0;
        $classInstantiatorProviderCalls = 0;
        $receivedClassInstantiator = null;
        $receivedConfiguredServiceFactory = null;
        $provider = (new bootstrap_object_defaults_provider_factory(
            objectFactoryProvider: static function (callable $configuredServiceFactory) use (&$objectFactoryProviderCalls, &$receivedConfiguredServiceFactory): callable {
                ++$objectFactoryProviderCalls;
                $receivedConfiguredServiceFactory = $configuredServiceFactory;

                return static fn(string $className, array $arguments): object => $configuredServiceFactory($className, $arguments);
            },
            configuredServiceFactoryProvider: static function (callable $classInstantiator) use (&$configuredServiceFactoryProviderCalls, &$receivedClassInstantiator): callable {
                ++$configuredServiceFactoryProviderCalls;
                $receivedClassInstantiator = $classInstantiator;

                return static fn(string $className, array $arguments): object => $classInstantiator($className, $arguments);
            },
            classInstantiatorProvider: static function () use (&$classInstantiatorProviderCalls): callable {
                ++$classInstantiatorProviderCalls;

                return static fn(string $className, array $arguments): object => new $className('provider-' . $arguments[0]);
            }
        ))();

        $factory = $provider->objectFactory();
        $object = $factory(BootstrapObjectDefaultsProviderFactoryProbe::class, ['config.php']);

        $this->assertSame(1, $objectFactoryProviderCalls);
        $this->assertSame(1, $configuredServiceFactoryProviderCalls);
        $this->assertSame(1, $classInstantiatorProviderCalls);
        $this->assertIsCallable($receivedClassInstantiator);
        $this->assertIsCallable($receivedConfiguredServiceFactory);
        $this->assertInstanceOf(BootstrapObjectDefaultsProviderFactoryProbe::class, $object);
        $this->assertSame('provider-config.php', $object->configPath);
    }

    public function testSourceOwnsBootstrapObjectDefaultAssembly(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/factory/bootstrap_object_defaults_provider_factory.php');
        $configuredServiceProviderSource = file_get_contents(dirname(__DIR__, 3) . '/core/factory/bootstrap_object_configured_service_provider.php');
        $classInstantiatorProviderSource = file_get_contents(dirname(__DIR__, 3) . '/core/factory/bootstrap_object_class_instantiator_provider.php');

        $this->assertIsString($source);
        $this->assertIsString($configuredServiceProviderSource);
        $this->assertIsString($classInstantiatorProviderSource);
        $this->assertStringContainsString('final class bootstrap_object_defaults_provider_factory', $source);
        $this->assertStringContainsString('private \Closure $objectFactoryFactory;', $source);
        $this->assertStringContainsString('private \Closure $configuredServiceFactoryFactory;', $source);
        $this->assertStringContainsString('private \Closure $classInstantiatorFactory;', $source);
        $this->assertStringContainsString('private \Closure $objectFactoryProvider;', $source);
        $this->assertStringContainsString('private \Closure $configuredServiceFactoryProvider;', $source);
        $this->assertStringContainsString('private \Closure $classInstantiatorProvider;', $source);
        $this->assertStringContainsString('public function __construct(', $source);
        $this->assertStringContainsString('?callable $objectFactoryProvider = null,', $source);
        $this->assertStringContainsString('?callable $configuredServiceFactoryProvider = null,', $source);
        $this->assertStringContainsString('?callable $classInstantiatorProvider = null', $source);
        $this->assertStringContainsString('$this->objectFactoryProvider = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->configuredServiceFactoryProvider = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->classInstantiatorProvider = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->objectFactoryFactory = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->configuredServiceFactoryFactory = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('$this->classInstantiatorFactory = \Closure::fromCallable(', $source);
        $this->assertStringContainsString('public function __invoke(): bootstrap_object_defaults_factory', $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/bootstrap_object_defaults_factory.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/bootstrap_object_factory.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/../factory/configured_service_factory.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/../di/configured_class_instantiator.php';", $source);
        $this->assertStringContainsString('$classInstantiatorFactory = $this->classInstantiatorFactory;', $source);
        $this->assertStringContainsString('$configuredServiceFactoryFactory = $this->configuredServiceFactoryFactory;', $source);
        $this->assertStringContainsString('$objectFactoryFactory = $this->objectFactoryFactory;', $source);
        $this->assertStringContainsString('$classInstantiator = $classInstantiatorFactory();', $source);
        $this->assertStringContainsString('$configuredServiceFactory = $configuredServiceFactoryFactory($classInstantiator);', $source);
        $this->assertStringContainsString('$objectFactory = $objectFactoryFactory($configuredServiceFactory);', $source);
        $this->assertStringContainsString('return new bootstrap_object_defaults_factory(', $source);
        $this->assertStringContainsString('?? fn(callable $configuredServiceFactory): callable => ($this->objectFactoryProvider)($configuredServiceFactory)', $source);
        $this->assertStringContainsString('?? fn(callable $classInstantiator): callable => ($this->configuredServiceFactoryProvider)($classInstantiator)', $source);
        $this->assertStringContainsString('?? fn(): callable => ($this->classInstantiatorProvider)()', $source);
        $this->assertStringContainsString('?? static fn(callable $configuredServiceFactory): callable => new bootstrap_object_factory($configuredServiceFactory)', $source);
        $this->assertStringContainsString('?? new bootstrap_object_configured_service_provider()', $source);
        $this->assertStringContainsString('?? new bootstrap_object_class_instantiator_provider()', $source);
        $this->assertStringNotContainsString('new configured_service_factory(', $source);
        $this->assertStringNotContainsString('new configured_class_instantiator(', $source);
        $this->assertStringNotContainsString('new reflection_class_factory()', $source);
        $this->assertStringContainsString('final class bootstrap_object_configured_service_provider', $configuredServiceProviderSource);
        $this->assertStringContainsString('return new configured_service_factory($classInstantiator);', $configuredServiceProviderSource);
        $this->assertStringContainsString('final class bootstrap_object_class_instantiator_provider', $classInstantiatorProviderSource);
        $this->assertStringContainsString('return new configured_class_instantiator(new reflection_class_factory());', $classInstantiatorProviderSource);
        $this->assertStringNotContainsString('private ?\Closure $objectFactoryFactory', $source);
        $this->assertStringNotContainsString('private ?\Closure $configuredServiceFactoryFactory', $source);
        $this->assertStringNotContainsString('private ?\Closure $classInstantiatorFactory', $source);
        $this->assertStringNotContainsString('private ?\Closure $objectFactoryProvider', $source);
        $this->assertStringNotContainsString('private ?\Closure $configuredServiceFactoryProvider', $source);
        $this->assertStringNotContainsString('private ?\Closure $classInstantiatorProvider', $source);
        $this->assertStringNotContainsString('$objectFactoryFactory === null ? null : \Closure::fromCallable($objectFactoryFactory)', $source);
        $this->assertStringNotContainsString('$configuredServiceFactoryFactory === null ? null : \Closure::fromCallable($configuredServiceFactoryFactory)', $source);
        $this->assertStringNotContainsString('$classInstantiatorFactory === null ? null : \Closure::fromCallable($classInstantiatorFactory)', $source);
        $this->assertStringNotContainsString('$objectFactoryProvider === null ? null : \Closure::fromCallable($objectFactoryProvider)', $source);
        $this->assertStringNotContainsString('$configuredServiceFactoryProvider === null ? null : \Closure::fromCallable($configuredServiceFactoryProvider)', $source);
        $this->assertStringNotContainsString('$classInstantiatorProvider === null ? null : \Closure::fromCallable($classInstantiatorProvider)', $source);
        $this->assertStringNotContainsString('private function objectFactoryFactory(): \Closure', $source);
        $this->assertStringNotContainsString('private function configuredServiceFactoryFactory(): \Closure', $source);
        $this->assertStringNotContainsString('private function classInstantiatorFactory(): \Closure', $source);
        $this->assertStringNotContainsString('private function objectFactoryProvider(): \Closure', $source);
        $this->assertStringNotContainsString('private function configuredServiceFactoryProvider(): \Closure', $source);
        $this->assertStringNotContainsString('private function classInstantiatorProvider(): \Closure', $source);
        $this->assertStringNotContainsString("new bootstrap_object_factory(\n                new \\fan\\core\\di\\configured_service_factory(", $source);
    }
}

final class BootstrapObjectDefaultsProviderFactoryProbe
{
    public function __construct(
        public string $configPath
    ) {
    }
}
