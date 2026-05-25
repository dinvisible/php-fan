<?php

declare(strict_types=1);

use fan\core\service\image_modify;
use fan\core\service\image_modify_state;
use FanTest\_core\SourceFileContractTestCase;

class ServiceImageModifyTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/service/image_modify.php';

    public function testConstructorUsesInjectedStateAndBaseServiceDependencies(): void
    {
        $this->ensureBaseHelper();
        $state = new image_modify_state();
        $runtime = new ServiceImageModifyRuntimeDouble();
        $configurator = new ServiceImageModifyConfiguratorDouble(new ServiceImageModifyConfigDouble());
        $cacheFactoryCalls = [];

        $image = new ServiceImageModifyConstructorProbe(
            $state,
            $runtime,
            $configurator,
            static function (string $type) use (&$cacheFactoryCalls): object {
                $cacheFactoryCalls[] = $type;

                return (object)['type' => $type];
            }
        );

        $this->assertSame([ServiceImageModifyConstructorProbe::class], $runtime->initializer->serviceParams);
        $this->assertSame([$image], $configurator->getServiceConfigCalls);
        $this->assertSame([
            [ServiceImageModifyConstructorProbe::class, 'ENABLED'],
        ], $configurator->resetCalls);
        $this->assertFalse($state->hasInstances());
        $this->assertSame([], $cacheFactoryCalls);
    }

    public function testImageModifyServiceUsesInjectedStateAndBaseDependencies(): void
    {
        $source = $this->sourceCode();

        $this->assertStringNotContainsString('new image_modify_state()', $source);
        $this->assertStringNotContainsString('private static ?array $instances', $source);
        $this->assertStringNotContainsString('empty(self::$instances)', $source);
        $this->assertStringNotContainsString('new \fan\core\adapter\image_metadata_reader()', $source);
        $this->assertStringNotContainsString('new \fan\core\adapter\image_resource_factory()', $source);
        $this->assertStringNotContainsString('new \fan\core\adapter\image_canvas_operations()', $source);
        $this->assertStringNotContainsString('new \fan\core\adapter\image_output_writer()', $source);
        $this->assertStringNotContainsString('new fatalException', $source);
        $this->assertStringNotContainsString('use fan\project\exception\service\fatal as fatalException;', $source);
        $this->assertStringNotContainsString('array_val(', $source);
    }

    public function testConstructorRequiresInjectedState(): void
    {
        $this->ensureBaseHelper();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Image modify state is not configured for image modify service.');

        new image_modify(
            null,
            [],
            null,
            new ServiceImageModifyRuntimeDouble(),
            new ServiceImageModifyRuntimeDouble(),
            new ServiceImageModifyConfiguratorDouble(new ServiceImageModifyConfigDouble()),
            static fn(string $type): object => (object)['type' => $type],
            new stdClass(),
            new stdClass(),
            new stdClass(),
            new stdClass(),
            new stdClass()
        );
    }

    public function testSourceFileChecksAreInjected(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('$this->imageSourceFileStorage()->exists($sourcePath)', $source);
        $this->assertStringContainsString('$this->imageSourceFileStorage()->isReadable($sourcePath)', $source);
        $this->assertStringContainsString('$this->imageSourceFileStorage()->rename(', $source);
        $this->assertStringContainsString("throw new \RuntimeException('Image source file storage is not configured for image modify service.');", $source);
        $this->assertDoesNotMatchRegularExpression(
            '/(?<!->)(?<!::)(?<!\\\\)\b(?:file_exists|is_readable|rename)\s*\(/',
            $source
        );
    }

    public function testSetParamUpdatesTypeAndDimensions(): void
    {
        $image = new ServiceImageModifyProbe(10, 20);

        $this->assertSame($image, $image->setParam([
            'type' => 'png',
            'quality' => 150,
            'width' => 40,
            'height' => 30,
        ]));

        $this->assertSame('png', $image->getType());
        $this->assertSame(40.0, $image->getWidth());
        $this->assertSame(30.0, $image->getHeigth());
        $this->assertSame(10, $image->getSourceWidth());
        $this->assertSame(20, $image->getSourceHeigth());
    }

    public function testScaleCalculatesMissingHeightAndReplacesImage(): void
    {
        $image = new ServiceImageModifyProbe(100, 50);
        $width = 50;
        $height = null;

        $this->assertSame($image, $image->scal($width, $height));

        $this->assertSame(50, $width);
        $this->assertSame(25.0, $height);
        $this->assertSame(50, $image->getWidth());
        $this->assertSame(25.0, $image->getHeigth());
    }

    public function testScaleUsesInjectedCanvasOperations(): void
    {
        $canvasOperations = new ServiceImageModifyCanvasOperationsDouble();
        $image = new ServiceImageModifyProbe(100, 50, canvasOperations: $canvasOperations);
        $width = 50;
        $height = null;

        $image->scal($width, $height);

        $this->assertSame([[50, 25]], $canvasOperations->createTrueColorCalls);
        $this->assertSame([
            [0, 0, 0, 0, 50, 25, 100, 50],
        ], $canvasOperations->copyResampledCalls);
    }

    public function testCropNormalizesCoordinatesAndUpdatesSize(): void
    {
        $image = new ServiceImageModifyProbe(100, 80);

        $image->crop(-5, 10, 20, 0);

        $this->assertSame(20, $image->getWidth());
        $this->assertSame(70, $image->getHeigth());
    }

    public function testAdaptColorAcceptsIntegerStringAndRgbArray(): void
    {
        $image = new ServiceImageModifyProbe(10, 10);

        $this->assertSame(['red' => 18, 'green' => 52, 'blue' => 86], $image->colorParts(0x123456));
        $this->assertSame(['red' => 170, 'green' => 187, 'blue' => 204], $image->colorParts('AABBCC'));
        $this->assertSame(['red' => 1, 'green' => 2, 'blue' => 3], $image->colorParts(['R' => 1, 'G' => 2, 'B' => 3]));
    }

    public function testSetSourceParsesPathThroughInjectedRuntime(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'fan-image-');
        $this->assertIsString($file);
        $png = imagecreatetruecolor(3, 2);
        imagepng($png, $file);

        $runtime = new ServiceImageModifyRuntimeDouble();
        $image = new ServiceImageModifyProbe(
            1,
            1,
            $runtime,
            new ServiceImageModifyMetadataReaderDouble([3, 2, 3]),
            new ServiceImageModifyResourceFactoryDouble()
        );

        try {
            $image->setSource($file);
        } finally {
            @unlink($file);
        }

        $this->assertSame([$file], $runtime->paths);
        $this->assertSame(3, $image->getSourceWidth());
        $this->assertSame(2, $image->getSourceHeigth());
    }

    public function testSetSourceUsesInjectedImageRuntimeAdapters(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'fan-image-');
        $this->assertIsString($file);
        $png = imagecreatetruecolor(3, 2);
        imagepng($png, $file);

        $metadataReader = new ServiceImageModifyMetadataReaderDouble([3, 2, 3]);
        $resourceFactory = new ServiceImageModifyResourceFactoryDouble();
        $image = new ServiceImageModifyProbe(1, 1, new ServiceImageModifyRuntimeDouble(), $metadataReader, $resourceFactory);

        try {
            $image->setSource($file);
        } finally {
            @unlink($file);
        }

        $this->assertSame([$file], $metadataReader->paths);
        $this->assertSame([$file], $resourceFactory->typePaths);
        $this->assertSame([[$file, 'png']], $resourceFactory->createFromTypeCalls);
        $this->assertSame(3, $image->getSourceWidth());
        $this->assertSame(2, $image->getSourceHeigth());
    }

    public function testMarkeringUsesInjectedImageMetadataReader(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'fan-marker-');
        $this->assertIsString($file);
        $png = imagecreatetruecolor(1, 1);
        imagepng($png, $file);

        $runtime = new ServiceImageModifyRuntimeDouble();
        $metadataReader = new ServiceImageModifyMetadataReaderDouble([1, 1, 3]);
        $resourceFactory = new ServiceImageModifyResourceFactoryDouble();
        $canvasOperations = new ServiceImageModifyCanvasOperationsDouble();
        $image = new ServiceImageModifyProbe(10, 10, $runtime, $metadataReader, $resourceFactory, $canvasOperations);
        $image->setConfigObject(new ServiceImageModifyArrayConfigDouble([
            'WATERMARK_PATH' => $file,
        ]));

        try {
            $this->assertSame($image, $image->markering());
        } finally {
            @unlink($file);
        }

        $this->assertSame([$file], $runtime->paths);
        $this->assertSame([$file], $metadataReader->paths);
        $this->assertSame([[$file, 'png']], $resourceFactory->createFromTypeCalls);
        $this->assertSame([[10, -1, 0, 0, 1, 1, 10]], $canvasOperations->copyMergeCalls);
    }

    public function testSaveAsNewUsesInjectedImageOutputWriter(): void
    {
        $outputWriter = new ServiceImageModifyOutputWriterDouble();
        $image = new ServiceImageModifyProbe(10, 10, outputWriter: $outputWriter);

        $this->assertSame($image, $image->saveAsNew('/tmp/out.png'));

        $this->assertCount(1, $outputWriter->writeCalls);
        $this->assertSame('png', $outputWriter->writeCalls[0]['type']);
        $this->assertSame('/tmp/out.png', $outputWriter->writeCalls[0]['path']);
        $this->assertSame(80, $outputWriter->writeCalls[0]['quality']);
    }

    public function testSaveAsNewUsesInjectedServiceExceptionFactoryWhenOutputWriterFails(): void
    {
        $outputWriter = new ServiceImageModifyOutputWriterDouble(false);
        $image = new ServiceImageModifyProbe(10, 10, outputWriter: $outputWriter);
        $factoryCalls = [];
        $image->setServiceDependencies(
            serviceExceptionFactory: static function (
                string $exceptionClass,
                object $service,
                string $message,
                int $code,
                ?Throwable $previous
            ) use (&$factoryCalls): Throwable {
                $factoryCalls[] = [$exceptionClass, $service, $message, $code, $previous];

                return new RuntimeException($message, $code, $previous);
            }
        );

        try {
            $image->saveAsNew('/tmp/out.bad');
            $this->fail('Expected injected service exception factory to create the save failure.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Incorrect image type (png).', $exception->getMessage());
        }

        $this->assertCount(1, $factoryCalls);
        $this->assertSame('\fan\project\exception\service\fatal', $factoryCalls[0][0]);
        $this->assertSame($image, $factoryCalls[0][1]);
        $this->assertSame('Incorrect image type (png).', $factoryCalls[0][2]);
        $this->assertSame(E_USER_ERROR, $factoryCalls[0][3]);
        $this->assertNull($factoryCalls[0][4]);
    }

    public function testSaveAndReplaceRenamesThroughInjectedSourceStorage(): void
    {
        $outputWriter = new ServiceImageModifyOutputWriterDouble();
        $sourceFileStorage = new ServiceImageModifySourceFileStorageDouble();
        $image = new ServiceImageModifyProbe(10, 10, outputWriter: $outputWriter, sourceFileStorage: $sourceFileStorage);
        $image->setSourcePathForSave('/tmp/source/photo.png');

        $this->assertSame($image, $image->saveAndReplace('bak'));

        $this->assertSame([
            ['/tmp/source/photo.png', '/tmp/source/photo.bak.png'],
        ], $sourceFileStorage->renameCalls);
        $this->assertSame('/tmp/source/photo.png', $outputWriter->writeCalls[0]['path']);
    }

    public function testSaveAndReplaceSkipsRenameWhenExtensionIsNull(): void
    {
        $outputWriter = new ServiceImageModifyOutputWriterDouble();
        $sourceFileStorage = new ServiceImageModifySourceFileStorageDouble();
        $image = new ServiceImageModifyProbe(10, 10, outputWriter: $outputWriter, sourceFileStorage: $sourceFileStorage);
        $image->setSourcePathForSave('/tmp/source/photo.png');

        $this->assertSame($image, $image->saveAndReplace(null));

        $this->assertSame([], $sourceFileStorage->renameCalls);
        $this->assertSame('/tmp/source/photo.png', $outputWriter->writeCalls[0]['path']);
    }

    public function testSourceUsesInjectedOutputWriterInsteadOfDynamicImageFunction(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('$this->imageOutputWriter()->write(', $source);
        $this->assertStringNotContainsString('$func = \'image\' . $this->type;', $source);
        $this->assertStringNotContainsString('$func($this->image', $source);
        $this->assertStringNotContainsString('function_exists($func)', $source);
    }

    private function ensureBaseHelper(): void
    {
        if (function_exists('fan\core\base\get_class_name')) {
            return;
        }

        eval('
            namespace fan\core\base;

            function get_class_name(string|object $object): ?string
            {
                if (is_object($object)) {
                    $object = get_class($object);
                }
                $parts = explode("\\\\", $object);

                return end($parts);
            }
        ');
    }
}

final class ServiceImageModifyConstructorProbe extends image_modify
{
    public function __construct(
        image_modify_state $state,
        object $runtime,
        object $serviceConfigurator,
        callable $serviceCacheFactory
    )
    {
        parent::__construct(
            null,
            [],
            $state,
            $runtime,
            $runtime,
            $serviceConfigurator,
            $serviceCacheFactory,
            new ServiceImageModifyMetadataReaderDouble([1, 1, IMAGETYPE_PNG]),
            new ServiceImageModifyResourceFactoryDouble(),
            new ServiceImageModifyCanvasOperationsDouble(),
            new ServiceImageModifyOutputWriterDouble(),
            new ServiceImageModifySourceFileStorageDouble(),
            static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default
        );
    }
}

class ServiceImageModifyProbe extends image_modify
{
    public function __construct(
        int $width,
        int $height,
        ?object $runtime = null,
        ?object $metadataReader = null,
        ?object $resourceFactory = null,
        ?object $canvasOperations = null,
        ?object $outputWriter = null,
        ?object $sourceFileStorage = null
    )
    {
        $this->runtime = $runtime;
        $this->imageMetadataReader = $metadataReader ?? new ServiceImageModifyMetadataReaderDouble([$width, $height, IMAGETYPE_PNG]);
        $this->imageResourceFactory = $resourceFactory ?? new ServiceImageModifyResourceFactoryDouble();
        $this->imageCanvasOperations = $canvasOperations ?? new ServiceImageModifyCanvasOperationsDouble();
        $this->imageOutputWriter = $outputWriter ?? new ServiceImageModifyOutputWriterDouble();
        $this->imageSourceFileStorage = $sourceFileStorage ?? new ServiceImageModifySourceFileStorageDouble();
        $this->arrayValueReader = static fn(array|\ArrayAccess $array, mixed $key, mixed $default = null): mixed => $array[$key] ?? $default;
        $this->sourceWidth = $width;
        $this->sourceHeight = $height;
        $this->width = $width;
        $this->height = $height;
        $this->image = imagecreatetruecolor($width, $height);
        $this->setParam(['type' => 'png']);
    }

    public function colorParts(int|string|array $color): array
    {
        return array_intersect_key(
            imagecolorsforindex($this->image, $this->adaptColor($color)),
            ['red' => true, 'green' => true, 'blue' => true]
        );
    }

    public function setConfigObject(object $config): void
    {
        $this->config = $config;
    }

    public function setSourcePathForSave(string $sourcePath): void
    {
        $this->sourcePath = $sourcePath;
    }
}

final class ServiceImageModifyRuntimeDouble
{
    public ServiceImageModifyInitializerDouble $initializer;
    public array $paths = [];

    public function __construct()
    {
        $this->initializer = new ServiceImageModifyInitializerDouble();
    }

    public function getInitializer(): ServiceImageModifyInitializerDouble
    {
        return $this->initializer;
    }

    public function parsePath(string $path): string
    {
        $this->paths[] = $path;

        return $path;
    }
}

final class ServiceImageModifyInitializerDouble
{
    public array $serviceParams = [];

    public function setServiceParam(string $className): void
    {
        $this->serviceParams[] = $className;
    }
}

final class ServiceImageModifyConfiguratorDouble
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

final class ServiceImageModifyConfigDouble
{
    public function get(mixed $key = null, mixed $default = null): mixed
    {
        return $default;
    }
}

final class ServiceImageModifyArrayConfigDouble implements ArrayAccess
{
    public function __construct(private array $values)
    {
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->values);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->values[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->values[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->values[$offset]);
    }
}

final class ServiceImageModifyMetadataReaderDouble
{
    public array $paths = [];

    public function __construct(private array|false $size)
    {
    }

    public function size(string $path): array|false
    {
        $this->paths[] = $path;

        return $this->size;
    }
}

final class ServiceImageModifyResourceFactoryDouble
{
    public array $typePaths = [];
    public array $createFromTypeCalls = [];

    public function type(string $path): int|false
    {
        $this->typePaths[] = $path;

        return IMAGETYPE_PNG;
    }

    public function createFromType(string $path, string $type): mixed
    {
        $this->createFromTypeCalls[] = [$path, $type];

        return imagecreatetruecolor(1, 1);
    }
}

final class ServiceImageModifySourceFileStorageDouble
{
    public array $existsPaths = [];
    public array $readablePaths = [];
    public array $renameCalls = [];

    public function exists(string $path): bool
    {
        $this->existsPaths[] = $path;

        return file_exists($path);
    }

    public function isReadable(string $path): bool
    {
        $this->readablePaths[] = $path;

        return is_readable($path);
    }

    public function rename(string $sourcePath, string $targetPath): bool
    {
        $this->renameCalls[] = [$sourcePath, $targetPath];

        return true;
    }
}

final class ServiceImageModifyCanvasOperationsDouble
{
    public array $createTrueColorCalls = [];
    public array $copyResampledCalls = [];
    public array $copyMergeCalls = [];
    public array $colorAllocateCalls = [];

    public function createTrueColor(int $width, int $height): GdImage
    {
        $this->createTrueColorCalls[] = [$width, $height];

        return imagecreatetruecolor($width, $height);
    }

    public function copyResampled(
        mixed $destination,
        mixed $source,
        int $destinationX,
        int $destinationY,
        int $sourceX,
        int $sourceY,
        int $destinationWidth,
        int $destinationHeight,
        int $sourceWidth,
        int $sourceHeight
    ): bool {
        $this->copyResampledCalls[] = [
            $destinationX,
            $destinationY,
            $sourceX,
            $sourceY,
            $destinationWidth,
            $destinationHeight,
            $sourceWidth,
            $sourceHeight,
        ];

        return true;
    }

    public function copyMerge(
        mixed $destination,
        mixed $source,
        int $destinationX,
        int $destinationY,
        int $sourceX,
        int $sourceY,
        int $sourceWidth,
        int $sourceHeight,
        int $opacity
    ): bool {
        $this->copyMergeCalls[] = [
            $destinationX,
            $destinationY,
            $sourceX,
            $sourceY,
            $sourceWidth,
            $sourceHeight,
            $opacity,
        ];

        return true;
    }

    public function colorAllocate(mixed $image, int $red, int $green, int $blue): int|false
    {
        $this->colorAllocateCalls[] = [$red, $green, $blue];

        return imagecolorallocate($image, $red, $green, $blue);
    }
}

final class ServiceImageModifyOutputWriterDouble
{
    public array $writeCalls = [];

    public function __construct(private bool $writeResult = true)
    {
    }

    public function write(mixed $image, ?string $type, ?string $path = null, int|float $quality = 80): bool
    {
        $this->writeCalls[] = [
            'type' => $type,
            'path' => $path,
            'quality' => $quality,
        ];

        return $this->writeResult;
    }
}
