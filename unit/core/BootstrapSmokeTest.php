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
            ['index_source', 'vendor_autoload', 'composer_autoload_helper', 'factory_class'],
            array_column($result['checks'], 'name')
        );
    }

    public function testBootstrapSmokeRendersTextSummary(): void
    {
        $result = php_fan_bootstrap_smoke(dirname(__DIR__, 2));

        $this->assertStringStartsWith('Bootstrap smoke: PASS', php_fan_bootstrap_smoke_render_text($result));
    }
}
