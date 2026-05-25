<?php

declare(strict_types=1);

use fan\core\di\application_utility_service_factory_defaults_provider;
use fan\core\di\date_service_factory;
use fan\core\di\image_modify_service_factory;
use fan\core\di\obfuscator_service_factory;
use fan\core\di\soap_service_factory;
use PHPUnit\Framework\TestCase;

final class ApplicationUtilityServiceFactoryDefaultsProviderTest extends TestCase
{
    public function testProviderCreatesUtilityFactoryDefaults(): void
    {
        $defaultsProvider = new application_utility_service_factory_defaults_provider(
            static fn(callable $configuredServiceFactory): callable => new obfuscator_service_factory(
                $configuredServiceFactory,
                new ApplicationUtilityServiceFactoryDefaultsProviderReflectionClassFactoryDouble()
            ),
            static fn(callable $configuredServiceFactory): callable => new image_modify_service_factory(
                $configuredServiceFactory,
                new ApplicationUtilityServiceFactoryDefaultsProviderReflectionClassFactoryDouble()
            ),
            static fn(callable $configuredServiceFactory): callable => new soap_service_factory(
                $configuredServiceFactory,
                new ApplicationUtilityServiceFactoryDefaultsProviderReflectionClassFactoryDouble()
            ),
            static fn(callable $configuredServiceFactory): callable => new date_service_factory(
                $configuredServiceFactory,
                new ApplicationUtilityServiceFactoryDefaultsProviderReflectionClassFactoryDouble()
            )
        );

        $this->assertIsCallable($defaultsProvider->obfuscatorServiceFactory()(self::configuredServiceFactory()));
        $this->assertIsCallable($defaultsProvider->imageModifyServiceFactory()(self::configuredServiceFactory()));
        $this->assertIsCallable($defaultsProvider->soapServiceFactory()(self::configuredServiceFactory()));
        $this->assertIsCallable($defaultsProvider->dateServiceFactory()(self::configuredServiceFactory()));
    }

    private static function configuredServiceFactory(): callable
    {
        return static fn(string $className, array $arguments = []): object => new stdClass();
    }
}

final class ApplicationUtilityServiceFactoryDefaultsProviderReflectionClassFactoryDouble
{
    public function create(object|string $object): ReflectionClass
    {
        return new ReflectionClass($object);
    }
}
