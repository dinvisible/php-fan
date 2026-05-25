<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\adapter\image_canvas_operations;
use fan\core\adapter\image_metadata_reader;
use fan\core\adapter\image_output_writer;
use fan\core\adapter\image_resource_factory;


final class application_image_adapter_defaults_provider
{
    private \Closure $imageMetadataReaderFactory;

    private \Closure $imageResourceFactoryFactory;

    private \Closure $imageCanvasOperationsFactory;

    private \Closure $imageOutputWriterFactory;

    public function __construct(
        ?callable $imageMetadataReaderFactory = null,
        ?callable $imageResourceFactoryFactory = null,
        ?callable $imageCanvasOperationsFactory = null,
        ?callable $imageOutputWriterFactory = null
    )
    {
        $this->imageMetadataReaderFactory = \Closure::fromCallable(
            $imageMetadataReaderFactory
                ?? static fn(container_interface $container): object => new image_metadata_reader(
                    $container->get('warning_capture')
                )
        );
        $this->imageResourceFactoryFactory = \Closure::fromCallable(
            $imageResourceFactoryFactory
                ?? static fn(container_interface $container): object => new image_resource_factory()
        );
        $this->imageCanvasOperationsFactory = \Closure::fromCallable(
            $imageCanvasOperationsFactory
                ?? static fn(container_interface $container): object => new image_canvas_operations()
        );
        $this->imageOutputWriterFactory = \Closure::fromCallable(
            $imageOutputWriterFactory
                ?? static fn(container_interface $container): object => new image_output_writer()
        );
    }

    public function imageMetadataReaderFactory(): callable
    {
        return $this->imageMetadataReaderFactory;
    }

    public function imageResourceFactoryFactory(): callable
    {
        return $this->imageResourceFactoryFactory;
    }

    public function imageCanvasOperationsFactory(): callable
    {
        return $this->imageCanvasOperationsFactory;
    }

    public function imageOutputWriterFactory(): callable
    {
        return $this->imageOutputWriterFactory;
    }
}
