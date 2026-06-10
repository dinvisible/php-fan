<?php

declare(strict_types=1);

use fan\core\di\application_client_service_factory_defaults_provider;
use fan\core\di\cookie_service_factory;
use fan\core\di\curl_service_factory;
use fan\core\di\rest_service_factory;
use PHPUnit\Framework\TestCase;

final class ApplicationClientServiceFactoryDefaultsProviderTest extends TestCase
{
    public function testProviderCreatesClientFactoryDefaults(): void
    {
        $defaultsProvider = new application_client_service_factory_defaults_provider(
            static fn(callable $configuredServiceFactory): callable => new curl_service_factory(
                $configuredServiceFactory,
                new ApplicationClientServiceFactoryDefaultsProviderReflectionClassFactoryDouble()
            ),
            static fn(callable $configuredServiceFactory): callable => new rest_service_factory(
                $configuredServiceFactory,
                new ApplicationClientServiceFactoryDefaultsProviderReflectionClassFactoryDouble()
            ),
            static fn(callable $configuredServiceFactory): callable => new cookie_service_factory(
                $configuredServiceFactory,
                new ApplicationClientServiceFactoryDefaultsProviderReflectionClassFactoryDouble()
            )
        );

        $this->assertIsCallable($defaultsProvider->curlServiceFactory()(self::configuredServiceFactory()));
        $this->assertIsCallable($defaultsProvider->restServiceFactory()(self::configuredServiceFactory()));
        $this->assertIsCallable($defaultsProvider->cookieServiceFactory()(self::configuredServiceFactory()));
    }

    private static function configuredServiceFactory(): callable
    {
        return static fn(string $className, array $arguments = []): object => new stdClass();
    }
}

final class ApplicationClientServiceFactoryDefaultsProviderReflectionClassFactoryDouble
{
    public function create(object|string $object): ReflectionClass
    {
        return new ReflectionClass($object);
    }
}
