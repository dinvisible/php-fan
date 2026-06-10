<?php

declare(strict_types=1);

use fan\core\di\application_core_service_factory_defaults_provider;
use fan\core\di\application_core_service_factory_defaults_provider_factory;
use PHPUnit\Framework\TestCase;

final class ApplicationCoreServiceFactoryDefaultsProviderFactoryTest extends TestCase
{
    public function testFactoryCreatesApplicationCoreServiceFactoryDefaultsProvider(): void
    {
        $provider = (new application_core_service_factory_defaults_provider_factory())();

        $this->assertInstanceOf(application_core_service_factory_defaults_provider::class, $provider);
        $this->assertIsCallable($provider->requestServiceFactory()(self::configuredServiceFactory()));
        $this->assertIsCallable($provider->roleServiceFactory()(self::configuredServiceFactory()));
        $this->assertIsCallable($provider->errorServiceFactory()(self::configuredServiceFactory()));
        $this->assertIsCallable($provider->reflectorServiceFactory()(self::configuredServiceFactory()));
        $this->assertIsCallable($provider->applicationServiceFactory()(self::configuredServiceFactory()));
        $this->assertIsCallable($provider->debugServiceFactory()(self::configuredServiceFactory()));
        $this->assertIsCallable($provider->headerServiceFactory()(self::configuredServiceFactory()));
        $this->assertIsCallable($provider->matcherServiceFactory()(self::configuredServiceFactory()));
        $this->assertIsCallable($provider->timerServiceFactory()(self::configuredServiceFactory()));
        $this->assertIsCallable($provider->plainServiceFactory()(self::configuredServiceFactory()));
        $this->assertIsCallable($provider->localeServiceFactory()(self::configuredServiceFactory()));
        $this->assertIsCallable($provider->timerProgramFactory()(self::configuredServiceFactory()));
    }

    public function testFactoryAcceptsInjectedCoreFactoryDefaults(): void
    {
        $defaults = [
            'requestServiceFactory' => static fn(callable $configuredServiceFactory): callable => static fn(): object => (object)['name' => 'request'],
            'roleServiceFactory' => static fn(callable $configuredServiceFactory): callable => static fn(): object => (object)['name' => 'role'],
            'errorServiceFactory' => static fn(callable $configuredServiceFactory): callable => static fn(): object => (object)['name' => 'error'],
            'reflectorServiceFactory' => static fn(callable $configuredServiceFactory): callable => static fn(): object => (object)['name' => 'reflector'],
            'applicationServiceFactory' => static fn(callable $configuredServiceFactory): callable => static fn(): object => (object)['name' => 'application'],
            'debugServiceFactory' => static fn(callable $configuredServiceFactory): callable => static fn(): object => (object)['name' => 'debug'],
            'headerServiceFactory' => static fn(callable $configuredServiceFactory): callable => static fn(): object => (object)['name' => 'header'],
            'matcherServiceFactory' => static fn(callable $configuredServiceFactory): callable => static fn(): object => (object)['name' => 'matcher'],
            'timerServiceFactory' => static fn(callable $configuredServiceFactory): callable => static fn(): object => (object)['name' => 'timer'],
            'plainServiceFactory' => static fn(callable $configuredServiceFactory): callable => static fn(): object => (object)['name' => 'plain'],
            'localeServiceFactory' => static fn(callable $configuredServiceFactory): callable => static fn(): object => (object)['name' => 'locale'],
            'timerProgramFactory' => static fn(callable $configuredServiceFactory): callable => static fn(): object => (object)['name' => 'timer-program'],
        ];
        $provider = (new application_core_service_factory_defaults_provider_factory(...array_values($defaults)))();

        $this->assertSame('request', $provider->requestServiceFactory()(self::configuredServiceFactory())()->name);
        $this->assertSame('role', $provider->roleServiceFactory()(self::configuredServiceFactory())()->name);
        $this->assertSame('error', $provider->errorServiceFactory()(self::configuredServiceFactory())()->name);
        $this->assertSame('reflector', $provider->reflectorServiceFactory()(self::configuredServiceFactory())()->name);
        $this->assertSame('application', $provider->applicationServiceFactory()(self::configuredServiceFactory())()->name);
        $this->assertSame('debug', $provider->debugServiceFactory()(self::configuredServiceFactory())()->name);
        $this->assertSame('header', $provider->headerServiceFactory()(self::configuredServiceFactory())()->name);
        $this->assertSame('matcher', $provider->matcherServiceFactory()(self::configuredServiceFactory())()->name);
        $this->assertSame('timer', $provider->timerServiceFactory()(self::configuredServiceFactory())()->name);
        $this->assertSame('plain', $provider->plainServiceFactory()(self::configuredServiceFactory())()->name);
        $this->assertSame('locale', $provider->localeServiceFactory()(self::configuredServiceFactory())()->name);
        $this->assertSame('timer-program', $provider->timerProgramFactory()(self::configuredServiceFactory())()->name);
    }
    private static function configuredServiceFactory(): callable
    {
        return static fn(string $className, array $arguments = []): object => new stdClass();
    }
}
