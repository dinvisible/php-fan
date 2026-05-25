<?php

declare(strict_types=1);

use fan\core\di\image_modify_service_factory;
use fan\core\service\image_draw;
use fan\core\service\image_modify;
use fan\core\service\image_modify_state;
use PHPUnit\Framework\TestCase;

if (!function_exists('get_class_name')) {
    function get_class_name(string|object $object): ?string
    {
        if (is_object($object)) {
            $object = get_class($object);
        }

        $parts = explode('\\', $object);

        return end($parts);
    }
}

final class ImageModifyServiceFactoryTest extends TestCase
{
    public function testFactoryCreatesCoreImageModifyServiceWithTypedConstructor(): void
    {
        $overrideCalls = [];
        $factory = new image_modify_service_factory(
            static function (string $className, array $arguments) use (&$overrideCalls): object {
                $overrideCalls[] = [$className, $arguments];

                return new stdClass();
            }
        );
        $state = new image_modify_state();
        $runtime = new ImageModifyServiceFactoryRuntimeDouble();
        $configurator = new ImageModifyServiceFactoryConfiguratorDouble(new ImageModifyServiceFactoryConfigDouble());
        $imageMetadataReader = new stdClass();
        $imageResourceFactory = new stdClass();
        $imageCanvasOperations = new stdClass();
        $imageOutputWriter = new stdClass();
        $imageSourceFileStorage = new stdClass();
        $cacheFactory = static fn(string $type): object => (object)['type' => $type];
        $arrayValueReader = static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default;

        $image = $factory(
            image_modify::class,
            null,
            [],
            $state,
            $runtime,
            $runtime,
            $configurator,
            $cacheFactory,
            $imageMetadataReader,
            $imageResourceFactory,
            $imageCanvasOperations,
            $imageOutputWriter,
            $imageSourceFileStorage,
            $arrayValueReader
        );

        $this->assertInstanceOf(image_modify::class, $image);
        $this->assertSame([], $overrideCalls);
        $this->assertSame([image_modify::class], $runtime->initializer->serviceParams);
        $this->assertSame([$image], $configurator->getServiceConfigCalls);
        $this->assertSame([['image_modify', 'ENABLED']], $configurator->resetCalls);
        $this->assertSame($state, $this->propertyValue($image, 'state'));
        $this->assertSame($runtime, $this->propertyValue($image, 'runtime'));
        $this->assertSame($imageMetadataReader, $this->propertyValue($image, 'imageMetadataReader'));
        $this->assertSame($imageResourceFactory, $this->propertyValue($image, 'imageResourceFactory'));
        $this->assertSame($imageCanvasOperations, $this->propertyValue($image, 'imageCanvasOperations'));
        $this->assertSame($imageOutputWriter, $this->propertyValue($image, 'imageOutputWriter'));
        $this->assertSame($imageSourceFileStorage, $this->propertyValue($image, 'imageSourceFileStorage'));
        $this->assertSame($arrayValueReader, $this->propertyValue($image, 'arrayValueReader'));
    }

    public function testFactoryCreatesCoreImageDrawServiceWithTypedConstructor(): void
    {
        $factory = new image_modify_service_factory(
            static fn(string $className, array $arguments): object => new stdClass()
        );
        $state = new image_modify_state();
        $runtime = new ImageModifyServiceFactoryRuntimeDouble();
        $configurator = new ImageModifyServiceFactoryConfiguratorDouble(new ImageModifyServiceFactoryConfigDouble());
        $cacheFactory = static fn(string $type): object => (object)['type' => $type];
        $arrayValueReader = static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default;

        $image = $factory(
            image_draw::class,
            null,
            [],
            $state,
            $runtime,
            $runtime,
            $configurator,
            $cacheFactory,
            new stdClass(),
            new stdClass(),
            new stdClass(),
            new stdClass(),
            new stdClass(),
            $arrayValueReader
        );

        $this->assertInstanceOf(image_draw::class, $image);
        $this->assertSame([image_draw::class], $runtime->initializer->serviceParams);
        $this->assertSame([$image], $configurator->getServiceConfigCalls);
        $this->assertSame([['image_draw', 'ENABLED']], $configurator->resetCalls);
    }

    public function testFactoryDelegatesConfiguredImageModifyServiceOverrides(): void
    {
        $calls = [];
        $factory = new image_modify_service_factory(
            static function (string $className, array $arguments) use (&$calls): object {
                $calls[] = [$className, $arguments];

                return new ImageModifyServiceFactoryProbe(...$arguments);
            }
        );
        $state = new stdClass();
        $runtime = new stdClass();
        $serviceRuntime = new stdClass();
        $configurator = new stdClass();
        $imageMetadataReader = new stdClass();
        $imageResourceFactory = new stdClass();
        $imageCanvasOperations = new stdClass();
        $imageOutputWriter = new stdClass();
        $imageSourceFileStorage = new stdClass();
        $cacheFactory = static fn(string $type): object => (object)['type' => $type];
        $arrayValueReader = static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default;

        $image = $factory(
            ImageModifyServiceFactoryProbe::class,
            '/tmp/source.png',
            ['width' => 100],
            $state,
            $runtime,
            $serviceRuntime,
            $configurator,
            $cacheFactory,
            $imageMetadataReader,
            $imageResourceFactory,
            $imageCanvasOperations,
            $imageOutputWriter,
            $imageSourceFileStorage,
            $arrayValueReader
        );

        $this->assertInstanceOf(ImageModifyServiceFactoryProbe::class, $image);
        $this->assertSame(ImageModifyServiceFactoryProbe::class, $calls[0][0]);
        $this->assertSame([
            '/tmp/source.png',
            ['width' => 100],
            $state,
            $runtime,
            $serviceRuntime,
            $configurator,
            $cacheFactory,
            $imageMetadataReader,
            $imageResourceFactory,
            $imageCanvasOperations,
            $imageOutputWriter,
            $imageSourceFileStorage,
            $arrayValueReader,
        ], $calls[0][1]);
        $this->assertSame('/tmp/source.png', $image->sourcePath);
        $this->assertSame(['width' => 100], $image->createParam);
        $this->assertSame($state, $image->state);
        $this->assertSame($runtime, $image->runtime);
        $this->assertSame($serviceRuntime, $image->serviceBootstrapRuntime);
        $this->assertSame($configurator, $image->serviceConfigurator);
        $this->assertSame($cacheFactory, $image->serviceCacheFactory);
        $this->assertSame($imageMetadataReader, $image->imageMetadataReader);
        $this->assertSame($imageResourceFactory, $image->imageResourceFactory);
        $this->assertSame($imageCanvasOperations, $image->imageCanvasOperations);
        $this->assertSame($imageOutputWriter, $image->imageOutputWriter);
        $this->assertSame($imageSourceFileStorage, $image->imageSourceFileStorage);
        $this->assertSame($arrayValueReader, $image->arrayValueReader);
    }

                private function propertyValue(object $object, string $propertyName): mixed
    {
        $property = new ReflectionProperty(image_modify::class, $propertyName);

        return $property->getValue($object);
    }
}

final class ImageModifyServiceFactoryProbe
{
    public function __construct(
        public ?string $sourcePath,
        public array $createParam,
        public object $state,
        public object $runtime,
        public object $serviceBootstrapRuntime,
        public object $serviceConfigurator,
        public $serviceCacheFactory,
        public object $imageMetadataReader,
        public object $imageResourceFactory,
        public object $imageCanvasOperations,
        public object $imageOutputWriter,
        public object $imageSourceFileStorage,
        public $arrayValueReader
    ) {
    }
}


final class ImageModifyServiceFactoryRuntimeDouble
{
    public ImageModifyServiceFactoryInitializerDouble $initializer;

    public function __construct()
    {
        $this->initializer = new ImageModifyServiceFactoryInitializerDouble();
    }

    public function getInitializer(): ImageModifyServiceFactoryInitializerDouble
    {
        return $this->initializer;
    }

    public function parsePath(string $path): string
    {
        return $path;
    }

    public function classNameResolver(): callable
    {
        return static fn(object $object): string => get_class_name($object);
    }
}

final class ImageModifyServiceFactoryInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class ImageModifyServiceFactoryConfiguratorDouble
{
    public array $getServiceConfigCalls = [];
    public array $resetCalls = [];

    public function __construct(private object $config)
    {
    }

    public function getServiceConfig(object $service): object
    {
        $this->getServiceConfigCalls[] = $service;

        return $this->config;
    }

    public function reset(string $className, string $key): void
    {
        $this->resetCalls[] = [$className, $key];
    }
}

final class ImageModifyServiceFactoryConfigDouble
{
    public function get(mixed $key = null, mixed $default = null): mixed
    {
        return $default;
    }
}
