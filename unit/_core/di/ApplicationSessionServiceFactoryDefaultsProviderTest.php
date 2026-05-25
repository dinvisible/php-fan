<?php

declare(strict_types=1);

use fan\core\di\application_session_service_factory_defaults_provider;
use fan\core\di\session_service_factory;
use PHPUnit\Framework\TestCase;
use fan\core\adapter\reflection_class_factory;


final class ApplicationSessionServiceFactoryDefaultsProviderTest extends TestCase
{
    public function testProviderCreatesSessionFactoryDefaults(): void
    {
        $defaultsProvider = new application_session_service_factory_defaults_provider(
            static fn(callable $configuredServiceFactory): callable => new session_service_factory(
                $configuredServiceFactory,
                new reflection_class_factory()
            )
        );

        $this->assertIsCallable($defaultsProvider->sessionServiceFactory()(self::configuredServiceFactory()));
    }

    private static function configuredServiceFactory(): callable
    {
        return static fn(string $className, array $arguments = []): object => new stdClass();
    }
}
