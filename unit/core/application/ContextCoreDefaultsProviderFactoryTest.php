<?php

declare(strict_types=1);

use fan\core\bootstrap\context;
use fan\core\di\container;
use fan\core\di\context_core_defaults_factory;
use fan\core\di\context_core_defaults_provider_factory;
use fan\core\di\context_defaults_factory;
use fan\core\service\bootstrap_runtime;
use fan\core\service\request_input;
use PHPUnit\Framework\TestCase;

final class ContextCoreDefaultsProviderFactoryTest extends TestCase
{
    public function testFactoryCreatesCoreContextDefaultsProvider(): void
    {
        $provider = (new context_core_defaults_provider_factory())();
        $defaults = $provider();

        $this->assertInstanceOf(context_core_defaults_factory::class, $provider);
        foreach ([
            'stateFactory',
            'containerFactory',
            'requestInputFactory',
            'bootstrapRuntimeFactory',
        ] as $key) {
            $this->assertArrayHasKey($key, $defaults);
            $this->assertIsCallable($defaults[$key]);
        }

        $context = new context(defaultFactoriesFactory: new context_defaults_factory());

        $this->assertInstanceOf(\fan\core\bootstrap\state::class, $defaults['stateFactory']());
        $this->assertInstanceOf(container::class, $defaults['containerFactory']($context));
        $this->assertInstanceOf(request_input::class, $defaults['requestInputFactory']());
        $this->assertInstanceOf(bootstrap_runtime::class, $defaults['bootstrapRuntimeFactory']($context));
    }

    public function testSourceOwnsGroupedCoreContextAssembly(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/factory/context_core_defaults_provider_factory.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('new bootstrap_state_defaults_factory()', $source);
        $this->assertStringContainsString('(new application_container_defaults_provider_factory())()', $source);
        $this->assertStringContainsString('new context_container_defaults_factory(', $source);
        $this->assertStringContainsString('new bootstrap_request_input_defaults_factory()', $source);
        $this->assertStringContainsString('new bootstrap_runtime_defaults_factory()', $source);
        $this->assertStringNotContainsString('context_core_state_defaults_provider_factory', $source);
        $this->assertStringNotContainsString('context_core_container_defaults_provider_factory', $source);
        $this->assertStringNotContainsString('context_core_request_input_defaults_provider_factory', $source);
        $this->assertStringNotContainsString('context_core_runtime_defaults_provider_factory', $source);
    }
}
