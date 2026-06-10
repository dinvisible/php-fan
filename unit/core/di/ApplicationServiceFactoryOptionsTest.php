<?php

declare(strict_types=1);

use fan\core\di\application_service_factory_options;
use PHPUnit\Framework\TestCase;

final class ApplicationServiceFactoryOptionsTest extends TestCase
{
    public function testOptionsKeepFactoryOverridesAsClosures(): void
    {
        $requestInputFactory = static fn(): object => (object)['name' => 'request'];

        $options = new application_service_factory_options(
            requestInputFactory: $requestInputFactory
        );

        $this->assertSame($requestInputFactory, $options->requestInputFactory);
        $this->assertNull($options->cacheEngineFactory);
    }

    public function testOptionsCanWrapGenericCallables(): void
    {
        $factory = new ApplicationServiceFactoryOptionsCallableDouble();

        $options = application_service_factory_options::fromCallables([
            'requestInputFactory' => [$factory, 'createRequestInput'],
        ]);

        $this->assertInstanceOf(\Closure::class, $options->requestInputFactory);
        $this->assertSame('request', ($options->requestInputFactory)()->name);
    }

    public function testSourceMovesCallableOverrideSurfaceOutOfContainerFactory(): void
    {
        $optionsSource = file_get_contents(dirname(__DIR__, 3) . '/core/factory/application_service_factory_options.php');
        $containerSource = file_get_contents(dirname(__DIR__, 3) . '/core/factory/application_container_factory.php');

        $this->assertIsString($optionsSource);
        $this->assertIsString($containerSource);
        $this->assertStringContainsString('final class application_service_factory_options', $optionsSource);
        $this->assertStringContainsString('public ?\Closure $requestInputFactory = null,', $optionsSource);
        $this->assertStringNotContainsString('application_service_factory_options $factoryOptions,', $containerSource);
        $this->assertStringNotContainsString('?application_service_factory_options $factoryOptions = null,', $containerSource);
        $this->assertStringNotContainsString('$factoryOptions ?? new application_service_factory_options()', $containerSource);
        $this->assertStringNotContainsString('?callable $requestInputFactory = null', $containerSource);
        $this->assertStringNotContainsString('?callable $configuredServiceFactory = null', $containerSource);
    }
}

final class ApplicationServiceFactoryOptionsCallableDouble
{
    public function createRequestInput(): object
    {
        return (object)['name' => 'request'];
    }
}
