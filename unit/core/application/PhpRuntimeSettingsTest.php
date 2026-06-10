<?php

declare(strict_types=1);

use fan\core\runtime\php_runtime_settings;
use PHPUnit\Framework\TestCase;

final class PhpRuntimeSettingsTest extends TestCase
{
    public function testSourceOwnsNativeIniAccess(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/runtime/php_runtime_settings.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class php_runtime_settings', $source);
        $this->assertStringContainsString('return PHP_VERSION;', $source);
        $this->assertStringContainsString('return php_sapi_name();', $source);
        $this->assertStringContainsString('exit($message);', $source);
        $this->assertStringContainsString('return ini_get($name);', $source);
        $this->assertStringContainsString('return ini_set($name, $value);', $source);
    }

    public function testSettingsAdapterIsUsable(): void
    {
        $settings = new php_runtime_settings();

        $this->assertNotSame('', $settings->version());
        $this->assertNotSame('', $settings->sapiName());
        $this->assertIsString($settings->get('date.timezone'));
    }
}
