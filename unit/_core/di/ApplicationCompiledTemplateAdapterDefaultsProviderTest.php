<?php

declare(strict_types=1);

use fan\core\di\application_compiled_template_adapter_defaults_provider;
use PHPUnit\Framework\TestCase;
use fan\core\adapter\compiled_template_loader;
use fan\core\adapter\compiled_template_loader_state;
use fan\core\di\container_interface;


final class ApplicationCompiledTemplateAdapterDefaultsProviderTest extends TestCase
{
    public function testProviderCreatesCompiledTemplateAdapterDefaults(): void
    {
        $provider = self::provider();
        $state = new compiled_template_loader_state();
        $container = self::containerWith(['compiled_template_loader_state' => $state]);

        $factories = [
            'compiledTemplateLoaderStateFactory' => compiled_template_loader_state::class,
            'compiledTemplateLoaderFactory' => compiled_template_loader::class,
        ];

        foreach ($factories as $method => $expectedClass) {
            $factory = $provider->{$method}();

            $this->assertInstanceOf(\Closure::class, \Closure::fromCallable($factory));
            $this->assertInstanceOf($expectedClass, $factory($container));
        }
    }
    private static function provider(): application_compiled_template_adapter_defaults_provider
    {
        return new application_compiled_template_adapter_defaults_provider();
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
