<?php

declare(strict_types=1);

use fan\core\di\bootstrap_runtime_service_defaults_factory;
use PHPUnit\Framework\TestCase;
use fan\core\di\bootstrap_runtime_service_factory;
use fan\core\service\bootstrap_runtime;


final class BootstrapRuntimeServiceDefaultsFactoryTest extends TestCase
{
    public function testFactoryCreatesBootstrapRuntimeServiceFactory(): void
    {
        $factory = new bootstrap_runtime_service_defaults_factory(
            static fn(): callable => new bootstrap_runtime_service_factory(
                static fn(
                    ?object $serviceListenerState = null,
                    ?object $serviceSingleState = null,
                    ?object $viewLoaderState = null,
                    ?object $metaMakerState = null,
                    ?object $specFileImageRowState = null,
                    ?callable $serviceEngineFactory = null,
                    array $bootstrapOperations = [],
                    ?callable $serviceExceptionFactory = null
                ): object => new bootstrap_runtime(
                    $serviceListenerState,
                    $serviceSingleState,
                    $viewLoaderState,
                    $metaMakerState,
                    $specFileImageRowState,
                    $serviceEngineFactory,
                    $bootstrapOperations,
                    $serviceExceptionFactory
                )
            )
        );

        $this->assertIsCallable($factory->runtimeServiceFactory());
        $this->assertInstanceOf(bootstrap_runtime::class, $factory->runtimeServiceFactory()());
    }

    public function testSourceOwnsBootstrapRuntimeServiceDefaultAssemblyBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/factory/bootstrap_runtime_service_defaults_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class bootstrap_runtime_service_defaults_factory', $source);
        $this->assertStringContainsString('private \Closure $runtimeServiceFactory;', $source);
        $this->assertStringContainsString('public function __construct(callable $runtimeServiceFactory)', $source);
        $this->assertStringContainsString('$this->runtimeServiceFactory = \Closure::fromCallable($runtimeServiceFactory);', $source);
        $this->assertStringContainsString('public function runtimeServiceFactory(): callable', $source);
        $this->assertStringContainsString('return ($this->runtimeServiceFactory)();', $source);
        $this->assertStringNotContainsString('public static function runtimeServiceFactory(): callable', $source);
        $this->assertStringNotContainsString('bootstrapRuntimeConstructor', $source);
        $this->assertStringNotContainsString('runtimeServiceClasses', $source);
        $this->assertStringNotContainsString('loadClass', $source);
        $this->assertStringNotContainsString('require_once', $source);
        $this->assertStringNotContainsString('new \fan\core\di\bootstrap_runtime_service_factory(', $source);
        $this->assertStringNotContainsString('new \fan\core\service\bootstrap_runtime(', $source);
    }
}
