<?php

declare(strict_types=1);

use fan\core\di\application_pager_service_factory_defaults_provider;
use fan\core\di\pager_service_factory;
use PHPUnit\Framework\TestCase;

final class ApplicationPagerServiceFactoryDefaultsProviderTest extends TestCase
{
    public function testProviderCreatesPagerFactoryDefaults(): void
    {
        $defaultsProvider = new application_pager_service_factory_defaults_provider(
            static fn(callable $configuredServiceFactory): callable => new pager_service_factory(
                $configuredServiceFactory,
                new ApplicationPagerServiceFactoryDefaultsProviderReflectionClassFactoryDouble()
            )
        );

        $this->assertIsCallable($defaultsProvider->pagerServiceFactory()(self::configuredServiceFactory()));
    }

    private static function configuredServiceFactory(): callable
    {
        return static fn(string $className, array $arguments = []): object => new stdClass();
    }
}

final class ApplicationPagerServiceFactoryDefaultsProviderReflectionClassFactoryDouble
{
    public function create(object|string $object): ReflectionClass
    {
        return new ReflectionClass($object);
    }
}
