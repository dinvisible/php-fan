<?php

declare(strict_types=1);

use fan\core\di\application_runtime_factory_defaults_provider;
use fan\core\di\application_runtime_factory_provider;
use fan\core\di\configured_class_instantiator;
use fan\core\di\configured_service_factory;
use PHPUnit\Framework\TestCase;
use fan\core\adapter\reflection_class_factory;


final class ApplicationRuntimeFactoryDefaultsProviderTest extends TestCase
{
    public function testProviderCreatesApplicationRuntimeFactoryProviderWithDefaultFactories(): void
    {
        $defaultsProvider = self::defaultsProvider();

        $runtimeFactoryProvider = $defaultsProvider->applicationRuntimeFactoryProvider();
        $this->assertInstanceOf(application_runtime_factory_provider::class, $runtimeFactoryProvider);
        $this->assertIsCallable($runtimeFactoryProvider->configuredConstructionBoundary());
        $this->assertIsCallable($runtimeFactoryProvider->requestInputFactory());
        $this->assertIsCallable($runtimeFactoryProvider->phpArrayFileLoader());
        $this->assertIsCallable($runtimeFactoryProvider->serializerOperationsFactory());
    }    private static function defaultsProvider(): application_runtime_factory_defaults_provider
    {
        return new application_runtime_factory_defaults_provider(
            static fn(callable ...$factories): application_runtime_factory_provider => new application_runtime_factory_provider(
                ...$factories
            ),
            new configured_service_factory(
                new configured_class_instantiator(new reflection_class_factory())
            ),
            static fn(): object => new stdClass(),
            static fn(string $path, mixed $default = null): mixed => $default,
            static fn(object $warningCapture): object => new stdClass()
        );
    }
}
