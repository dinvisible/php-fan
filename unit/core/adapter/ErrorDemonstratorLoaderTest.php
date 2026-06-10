<?php

declare(strict_types=1);

use fan\core\adapter\error_demonstrator_loader;
use PHPUnit\Framework\TestCase;

final class ErrorDemonstratorLoaderTest extends TestCase
{
    public function testSourceOwnsErrorDemonstratorClassLoadingBoundary(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/adapter/error_demonstrator_loader.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class error_demonstrator_loader', $source);
        $this->assertStringContainsString('private object $fileStorage', $source);
        $this->assertStringContainsString('require_once str_replace(\'{CORE_DIR}\', CORE_DIR, self::MAIN_ERROR_DEMONSTRATOR);', $source);
        $this->assertStringContainsString('$this->fileStorage()->isFile($projectPath)', $source);
        $this->assertStringContainsString('require_once $projectPath;', $source);
        $this->assertStringContainsString('new demonstrator($errMsg, $tplName, $input)', $source);
        $this->assertStringContainsString('new error_demonstrator($errMsg, $tplName, $input)', $source);
        $this->assertStringNotContainsString('is_file(', $source);
        $this->assertStringNotContainsString('include ', $source);
    }
}
