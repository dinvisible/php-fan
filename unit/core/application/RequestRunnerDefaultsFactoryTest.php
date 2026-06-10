<?php

declare(strict_types=1);

use fan\core\bootstrap\request_runner;
use fan\core\di\request_runner_defaults_factory;
use PHPUnit\Framework\TestCase;

final class RequestRunnerDefaultsFactoryTest extends TestCase
{
    public function testFactoryCreatesRequestRunner(): void
    {
        $factory = new request_runner_defaults_factory(
            static fn(): request_runner => new request_runner(
                static fn(): object => new RequestRunnerDefaultsFactoryApplicationStub(),
                static fn(object $application): callable => static function (): void {
                }
            )
        );

        $this->assertInstanceOf(request_runner::class, $factory());
    }

    public function testFactoryOwnsRequestRunnerDefaultDependencies(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/factory/request_runner_defaults_factory.php');
        $this->assertIsString($source);
        $this->assertStringContainsString('final class request_runner_defaults_factory', $source);
        $this->assertStringContainsString('private \Closure $requestRunnerFactory;', $source);
        $this->assertStringContainsString('public function __construct(?callable $requestRunnerFactory = null)', $source);
        $this->assertStringContainsString('$this->requestRunnerFactory = \Closure::fromCallable($requestRunnerFactory);', $source);
        $this->assertStringContainsString('public function __invoke(): request_runner', $source);
        $this->assertStringContainsString('return ($this->requestRunnerFactory)();', $source);
        $this->assertStringNotContainsString('loadDependencies', $source);
        $this->assertStringNotContainsString('require_once', $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/request_runner.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/bootstrap_application_defaults_provider_factory.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/bootstrap_application_defaults_factory.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/application_defaults_factory.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/application.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/bootstrap_context_setter.php';", $source);
        $this->assertStringNotContainsString('$applicationDefaultsProvider = (new bootstrap_application_defaults_provider_factory())();', $source);
        $this->assertStringNotContainsString('$applicationDefaults = $applicationDefaultsProvider->applicationDefaults();', $source);
        $this->assertStringNotContainsString('bootstrap_application_defaults_factory::applicationDefaults()', $source);
        $this->assertStringContainsString('(new bootstrap_application_defaults_factory())->applicationDefaults()', $source);
        $this->assertStringNotContainsString('new application_defaults_factory()', $source);
        $this->assertStringNotContainsString('new application()', $source);
        $this->assertStringContainsString('$applicationDefaults->applicationFactory()', $source);
        $this->assertStringContainsString('$applicationDefaults->errorHandlerFactory()', $source);
        $this->assertStringNotContainsString('new bootstrap_context_setter()', $source);
        $this->assertStringNotContainsString('\bootstrap::setContext($context)', $source);
        $this->assertStringNotContainsString('$application->logError(', $source);
    }
}

final class RequestRunnerDefaultsFactoryApplicationStub
{
    public function run(?string $configPath, bool $isEcho = true, ?callable $errorHandler = null): string
    {
        return 'ok';
    }

    public function context(): object
    {
        return new stdClass();
    }
}
