<?php

declare(strict_types=1);

use fan\core\di\application_service_sub_factory_defaults_provider;
use fan\core\di\application_service_sub_factory_defaults_provider_factory;
use PHPUnit\Framework\TestCase;

final class ApplicationServiceSubFactoryDefaultsProviderFactoryTest extends TestCase
{
    public function testFactoryCreatesApplicationServiceSubFactoryDefaultsProvider(): void
    {
        $provider = (new application_service_sub_factory_defaults_provider_factory())();

        $this->assertInstanceOf(application_service_sub_factory_defaults_provider::class, $provider);
        $this->assertIsCallable($provider->matcherItemFactory()());
        $this->assertIsCallable($provider->matcherItemComponentFactory()(self::configuredServiceFactory()));
        $this->assertIsCallable($provider->tabDelegateFactory()(self::configuredServiceFactory()));
        $this->assertIsCallable($provider->tabViewParserFactory()(self::configuredServiceFactory()));
        $this->assertFalse(method_exists($provider, 'templateTypeFactory'));
        $this->assertFalse(method_exists($provider, 'templateServiceFactory'));
        $this->assertIsCallable($provider->plainControllerFactory()(self::configuredServiceFactory()));
        $this->assertIsCallable($provider->blockFactory()(self::configuredServiceFactory()));
        $this->assertIsCallable($provider->blockExceptionFactory()(self::configuredServiceFactory()));
    }

    public function testFactoryAcceptsInjectedSubFactoryDefaults(): void
    {
        $defaults = [
            'matcherItemFactory' => static fn(): callable => static fn(): object => (object)['name' => 'matcher-item'],
            'matcherItemComponentFactory' => static fn(callable $configuredServiceFactory): callable => static fn(): object => (object)['name' => 'matcher-item-component'],
            'tabDelegateFactory' => static fn(callable $configuredServiceFactory): callable => static fn(): object => (object)['name' => 'tab-delegate'],
            'tabViewParserFactory' => static fn(callable $configuredServiceFactory): callable => static fn(): object => (object)['name' => 'tab-view-parser'],
            'plainControllerFactory' => static fn(callable $configuredServiceFactory): callable => static fn(): object => (object)['name' => 'plain-controller'],
            'blockFactory' => static fn(callable $configuredServiceFactory): callable => static fn(): object => (object)['name' => 'block'],
            'blockExceptionFactory' => static fn(callable $configuredServiceFactory): callable => static fn(): object => (object)['name' => 'block-exception'],
        ];
        $provider = (new application_service_sub_factory_defaults_provider_factory(...array_values($defaults)))();

        $this->assertSame('matcher-item', $provider->matcherItemFactory()()()->name);
        $this->assertSame('matcher-item-component', $provider->matcherItemComponentFactory()(self::configuredServiceFactory())()->name);
        $this->assertSame('tab-delegate', $provider->tabDelegateFactory()(self::configuredServiceFactory())()->name);
        $this->assertSame('tab-view-parser', $provider->tabViewParserFactory()(self::configuredServiceFactory())()->name);
        $this->assertSame('plain-controller', $provider->plainControllerFactory()(self::configuredServiceFactory())()->name);
        $this->assertSame('block', $provider->blockFactory()(self::configuredServiceFactory())()->name);
        $this->assertSame('block-exception', $provider->blockExceptionFactory()(self::configuredServiceFactory())()->name);
    }
    private static function configuredServiceFactory(): callable
    {
        return static fn(string $className, array $arguments = []): object => new stdClass();
    }
}
