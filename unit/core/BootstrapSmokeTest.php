<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/tools/bootstrap_smoke.php';

final class BootstrapSmokeTest extends TestCase
{
    public function testBootstrapSmokePassesForCurrentEntrypoint(): void
    {
        $result = php_fan_bootstrap_smoke(dirname(__DIR__, 2));

        $this->assertSame('pass', $result['status']);
        $this->assertSame(
            [
                'index_source',
                'vendor_autoload',
                'composer_autoload_helper',
                'array_val_function',
                'web_initializer_factory_class',
                'context_factory_class',
                'service_listener_state_class',
            ],
            array_column($result['checks'], 'name')
        );
    }

    public function testComposerFallbackResolvesBootstrapFactoryNamespaceExactly(): void
    {
        $root = dirname(__DIR__, 2);

        php_fan_bootstrap_smoke($root);

        $this->assertSame(
            $root . '/core/factory/context_factory.php',
            php_fan_composer_autoload_path_for('fan\\core\\bootstrap\\context_factory')
        );
        $this->assertNull(
            php_fan_composer_autoload_path_for('fan\\core\\bootstrap\\web_application_initializer_defaults_factory')
        );
    }

    public function testComposerFallbackResolvesGenericCoreNamespace(): void
    {
        $root = dirname(__DIR__, 2);

        php_fan_bootstrap_smoke($root);

        $this->assertSame(
            $root . '/core/service/service_listener_state.php',
            php_fan_composer_autoload_path_for('fan\\core\\service\\service_listener_state')
        );
    }

    public function testComposerFallbackLoadsCoreFunctions(): void
    {
        php_fan_bootstrap_smoke(dirname(__DIR__, 2));

        $this->assertTrue(function_exists('array_val'));
    }

    public function testBootstrapSmokeRendersTextSummary(): void
    {
        $result = php_fan_bootstrap_smoke(dirname(__DIR__, 2));

        $this->assertStringStartsWith('Bootstrap smoke: PASS', php_fan_bootstrap_smoke_render_text($result));
    }
}
