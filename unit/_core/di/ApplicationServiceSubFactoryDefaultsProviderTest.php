<?php

declare(strict_types=1);

use fan\core\di\application_service_sub_factory_defaults_provider;
use PHPUnit\Framework\TestCase;

final class ApplicationServiceSubFactoryDefaultsProviderTest extends TestCase
{
    public function testProviderDelegatesSubFactoryDefaults(): void
    {
        $defaultsProvider = new application_service_sub_factory_defaults_provider(
            static fn(): callable => static fn(): object => (object)['name' => 'matcher_item_factory'],
            static fn(callable $configuredServiceFactory): callable => static fn(): object => (object)['name' => 'matcher_item_component_factory'],
            static fn(callable $configuredServiceFactory): callable => static fn(): object => (object)['name' => 'tab_delegate_factory'],
            static fn(callable $configuredServiceFactory): callable => static fn(): object => (object)['name' => 'tab_view_parser_factory'],
            static fn(callable $configuredServiceFactory): callable => static fn(): object => (object)['name' => 'plain_controller_factory'],
            static fn(callable $configuredServiceFactory): callable => static fn(): object => (object)['name' => 'block_factory'],
            static fn(callable $configuredServiceFactory): callable => static fn(): object => (object)['name' => 'block_exception_factory']
        );

        $this->assertSame('matcher_item_factory', $defaultsProvider->matcherItemFactory()()()->name);
        $this->assertSame('matcher_item_component_factory', $defaultsProvider->matcherItemComponentFactory()(self::configuredServiceFactory())()->name);
        $this->assertSame('tab_delegate_factory', $defaultsProvider->tabDelegateFactory()(self::configuredServiceFactory())()->name);
        $this->assertSame('tab_view_parser_factory', $defaultsProvider->tabViewParserFactory()(self::configuredServiceFactory())()->name);
        $this->assertFalse(method_exists($defaultsProvider, 'templateTypeFactory'));
        $this->assertFalse(method_exists($defaultsProvider, 'templateServiceFactory'));
        $this->assertSame('plain_controller_factory', $defaultsProvider->plainControllerFactory()(self::configuredServiceFactory())()->name);
        $this->assertSame('block_factory', $defaultsProvider->blockFactory()(self::configuredServiceFactory())()->name);
        $this->assertSame('block_exception_factory', $defaultsProvider->blockExceptionFactory()(self::configuredServiceFactory())()->name);
    }    private static function configuredServiceFactory(): callable
    {
        return static fn(string $className, array $arguments = []): object => new stdClass();
    }
}
