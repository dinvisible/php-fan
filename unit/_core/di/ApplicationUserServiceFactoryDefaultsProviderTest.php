<?php

declare(strict_types=1);

use fan\core\di\application_user_service_factory_defaults_provider;
use fan\core\di\user_service_factory;
use PHPUnit\Framework\TestCase;
use fan\core\adapter\reflection_class_factory;


final class ApplicationUserServiceFactoryDefaultsProviderTest extends TestCase
{
    public function testProviderCreatesUserFactoryDefaults(): void
    {
        $defaultsProvider = new application_user_service_factory_defaults_provider(
            static fn(callable $configuredServiceFactory): callable => new user_service_factory(
                $configuredServiceFactory,
                new reflection_class_factory()
            )
        );

        $this->assertIsCallable($defaultsProvider->userServiceFactory()(self::configuredServiceFactory()));
    }

    private static function configuredServiceFactory(): callable
    {
        return static fn(string $className, array $arguments = []): object => new stdClass();
    }
}
