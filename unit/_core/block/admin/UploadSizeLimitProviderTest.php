<?php

declare(strict_types=1);

use fan\core\block\admin\upload_size_limit_provider;
use FanTest\_core\SourceFileContractTestCase;

final class BlockAdminUploadSizeLimitProviderTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/block/admin/upload_size_limit_provider.php';

    public function testProviderReadsUploadMaxFilesizeThroughInjectedRuntimeSettings(): void
    {
        $settings = new BlockAdminUploadSizeLimitRuntimeSettingsDouble(['upload_max_filesize' => '16M']);
        $provider = new upload_size_limit_provider($settings);

        $this->assertSame('16M', $provider());
        $this->assertSame(['upload_max_filesize'], $settings->gets);
    }

    public function testSourceUsesRuntimeSettingsBoundary(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('final class upload_size_limit_provider', $source);
        $this->assertStringContainsString("get('upload_max_filesize')", $source);
        $this->assertStringContainsString('public function __construct(object $phpRuntimeSettings)', $source);
        $this->assertStringNotContainsString('ini_get(', $source);
        $this->assertStringNotContainsString('defaultPhpRuntimeSettings', $source);
        $this->assertStringNotContainsString('php_runtime_settings.php', $source);
        $this->assertStringNotContainsString('new \fan\core\runtime\php_runtime_settings()', $source);
    }

    public function testAwareTraitRequiresInjectedProvider(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/_core/block/admin/upload_size_limit_provider_aware_trait.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('public function setUploadSizeLimitProvider(callable $uploadSizeLimitProvider): static', $source);
        $this->assertStringContainsString("throw new \RuntimeException('Upload size limit provider dependency is not configured for upload block.');", $source);
        $this->assertStringNotContainsString('defaultUploadSizeLimitProvider', $source);
        $this->assertStringNotContainsString('new upload_size_limit_provider()', $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/upload_size_limit_provider.php';", $source);
    }
}

final class BlockAdminUploadSizeLimitRuntimeSettingsDouble
{
    public array $gets = [];

    public function __construct(private array $values)
    {
    }

    public function get(string $name): string|false
    {
        $this->gets[] = $name;

        return $this->values[$name] ?? false;
    }
}
