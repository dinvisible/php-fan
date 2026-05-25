<?php

declare(strict_types=1);

use fan\core\di\application_image_adapter_defaults_provider;
use PHPUnit\Framework\TestCase;
use fan\core\adapter\image_canvas_operations;
use fan\core\adapter\image_metadata_reader;
use fan\core\adapter\image_output_writer;
use fan\core\adapter\image_resource_factory;
use fan\core\adapter\warning_capture;
use fan\core\di\container_interface;


final class ApplicationImageAdapterDefaultsProviderTest extends TestCase
{
    public function testProviderCreatesImageAdapterDefaults(): void
    {
        $provider = new application_image_adapter_defaults_provider();
        $warningCapture = new warning_capture();
        $container = self::containerWith(['warning_capture' => $warningCapture]);

        $factories = [
            'imageMetadataReaderFactory' => image_metadata_reader::class,
            'imageResourceFactoryFactory' => image_resource_factory::class,
            'imageCanvasOperationsFactory' => image_canvas_operations::class,
            'imageOutputWriterFactory' => image_output_writer::class,
        ];

        foreach ($factories as $method => $expectedClass) {
            $factory = $provider->{$method}();

            $this->assertInstanceOf(\Closure::class, \Closure::fromCallable($factory));
            $this->assertInstanceOf($expectedClass, $factory($container));
        }
    }

    public function testProviderAcceptsInjectedImageAdapterFactories(): void
    {
        $metadataReader = (object)['name' => 'metadata'];
        $resourceFactory = (object)['name' => 'resource'];
        $canvasOperations = (object)['name' => 'canvas'];
        $outputWriter = (object)['name' => 'output'];
        $container = self::containerWith([]);
        $provider = new application_image_adapter_defaults_provider(
            static fn(container_interface $container): object => $metadataReader,
            static fn(container_interface $container): object => $resourceFactory,
            static fn(container_interface $container): object => $canvasOperations,
            static fn(container_interface $container): object => $outputWriter
        );

        $this->assertSame($metadataReader, $provider->imageMetadataReaderFactory()($container));
        $this->assertSame($resourceFactory, $provider->imageResourceFactoryFactory()($container));
        $this->assertSame($canvasOperations, $provider->imageCanvasOperationsFactory()($container));
        $this->assertSame($outputWriter, $provider->imageOutputWriterFactory()($container));
    }
    private static function containerWith(array $dependencies): container_interface
    {
        return new class($dependencies) implements container_interface {
            public function __construct(private array $dependencies)
            {
            }

            public function has(string $id): bool
            {
                return array_key_exists($id, $this->dependencies);
            }

            public function get(string $id, mixed ...$arguments): mixed
            {
                return $this->dependencies[$id] ?? null;
            }
        };
    }
}
