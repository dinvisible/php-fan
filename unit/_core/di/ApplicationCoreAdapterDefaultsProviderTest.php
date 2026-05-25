<?php

declare(strict_types=1);

use fan\core\di\application_core_adapter_defaults_provider;
use PHPUnit\Framework\TestCase;
use fan\core\adapter\php_array_file_loader;
use fan\core\adapter\safe_serializer_operations;
use fan\core\adapter\warning_capture;


final class ApplicationCoreAdapterDefaultsProviderTest extends TestCase
{
    public function testProviderCreatesCoreAdapterDefaults(): void
    {
        $warningCapture = new warning_capture();
        $phpArrayFileLoader = new php_array_file_loader();
        $provider = new application_core_adapter_defaults_provider(
            $warningCapture,
            $phpArrayFileLoader,
            static fn(object $warningCapture): object => new safe_serializer_operations($warningCapture)
        );

        $this->assertSame($warningCapture, $provider->warningCapture());
        $this->assertSame($phpArrayFileLoader, $provider->phpArrayFileLoader());

        $factory = $provider->serializerOperationsFactory();

        $this->assertInstanceOf(\Closure::class, \Closure::fromCallable($factory));
        $this->assertInstanceOf(
            safe_serializer_operations::class,
            $factory($provider->warningCapture())
        );
    }
}
