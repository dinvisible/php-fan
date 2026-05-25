<?php

declare(strict_types=1);

use fan\core\di\application_content_service_factory_defaults_provider;
use fan\core\di\translation_service_factory;
use PHPUnit\Framework\TestCase;

final class ApplicationContentServiceFactoryDefaultsProviderTest extends TestCase
{
    public function testProviderCreatesContentFactoryDefaults(): void
    {
        $defaultsProvider = new application_content_service_factory_defaults_provider(
            static fn(callable $configuredServiceFactory): callable => new translation_service_factory(
                $configuredServiceFactory,
                new ApplicationContentServiceFactoryDefaultsProviderReflectionClassFactoryDouble()
            )
        );

        $this->assertIsCallable($defaultsProvider->translationServiceFactory()(self::configuredServiceFactory()));
    }

    private static function configuredServiceFactory(): callable
    {
        return static fn(string $className, array $arguments = []): object => new stdClass();
    }
}

final class ApplicationContentServiceFactoryDefaultsProviderReflectionClassFactoryDouble
{
    public function create(object|string $object): ReflectionClass
    {
        return new ReflectionClass($object);
    }
}
