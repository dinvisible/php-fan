<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class IndexSourceTest extends TestCase
{
    public function testRootIndexRunsWebInitializerAfterComposerAutoload(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/htdocs/index.php');

        $this->assertIsString($source);
        $this->assertStringContainsString("require_once __DIR__ . '/../vendor/autoload.php';", $source);
        $this->assertStringContainsString('use fan\core\di\web_application_initializer_defaults_factory;', $source);
        $this->assertStringContainsString('((new web_application_initializer_defaults_factory())())->run();', $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/autoload.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/../_core/factory/request_runner_defaults_provider_factory.php';", $source);
        $this->assertStringNotContainsString('$requestRunnerDefaults = new request_runner_defaults_factory();', $source);
        $this->assertStringNotContainsString('$requestRunner = $requestRunnerDefaults();', $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/../_core/factory/request_runner_defaults_factory.php';", $source);
        $this->assertStringNotContainsString('$requestRunner = (new \fan\core\bootstrap\request_runner_defaults_factory())();', $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/../_core/bootstrap.php';", $source);
        $this->assertStringNotContainsString('\bootstrap::run', $source);
        $this->assertStringNotContainsString('new \fan\core\bootstrap\application()', $source);
    }
}
