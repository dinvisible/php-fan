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
                'web_initializer_factory_class',
                'context_factory_class',
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

    public function testBootstrapSmokeRendersTextSummary(): void
    {
        $result = php_fan_bootstrap_smoke(dirname(__DIR__, 2));

        $this->assertStringStartsWith('Bootstrap smoke: PASS', php_fan_bootstrap_smoke_render_text($result));
    }
}
