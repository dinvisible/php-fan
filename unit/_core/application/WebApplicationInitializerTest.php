<?php

declare(strict_types=1);

use fan\core\bootstrap\request_runner;
use fan\core\bootstrap\web_application_initializer;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

final class WebApplicationInitializerTest extends TestCase
{
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testInitializerDefinesBaseDirAndRunsRequestRunner(): void
    {
        $application = new WebApplicationInitializerApplicationStub();
        $requestRunner = new request_runner(
            static fn(): object => $application,
            static fn(object $application): callable => static function (): void {
            }
        );
        $initializer = new web_application_initializer(
            $requestRunner,
            '/project/conf/bootstrap.php',
            '/tmp/php-fan-web'
        );

        $this->assertSame('ok', $initializer->run(false));
        $this->assertSame('/tmp/php-fan-web', BASE_DIR);
        $this->assertSame([['/project/conf/bootstrap.php', false, true]], $application->calls);
    }

    public function testSourceUsesInjectedRequestRunner(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/_core/application/web_application_initializer.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class web_application_initializer', $source);
        $this->assertStringContainsString('private readonly request_runner $requestRunner', $source);
        $this->assertStringContainsString("if (!defined('BASE_DIR'))", $source);
        $this->assertStringContainsString('define(\'BASE_DIR\', $this->baseDir);', $source);
        $this->assertStringContainsString('return $this->requestRunner->run($this->configPath, $isEcho);', $source);
        $this->assertStringNotContainsString('new request_runner_defaults_factory()', $source);
        $this->assertStringNotContainsString('require_once', $source);
    }
}

final class WebApplicationInitializerApplicationStub
{
    public array $calls = [];

    public function run(?string $configPath, bool $isEcho = true, ?callable $errorHandler = null): string
    {
        $this->calls[] = [$configPath, $isEcho, is_callable($errorHandler)];

        return 'ok';
    }

    public function context(): object
    {
        return new stdClass();
    }
}
