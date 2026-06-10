<?php

declare(strict_types=1);

use fan\core\bootstrap\request_runner;
use PHPUnit\Framework\TestCase;

final class RequestRunnerTest extends TestCase
{
    public function testRunnerUsesProvidedApplication(): void
    {
        $application = new RequestRunnerApplicationDouble();
        $factoryCalls = 0;
        $runner = new request_runner(
            static function () use (&$factoryCalls): object {
                $factoryCalls++;

                return new RequestRunnerApplicationDouble();
            },
            static fn(object $application): callable => static fn(): null => null
        );

        $this->assertSame('request-result', $runner->run('config.php', false, $application));
        $this->assertSame(0, $factoryCalls);
        $this->assertSame([['config.php', false, $application->errorHandler]], $application->runCalls);
    }

    public function testRunnerCreatesDefaultApplicationWhenNoneProvided(): void
    {
        $application = new RequestRunnerApplicationDouble();
        $runner = new request_runner(
            static fn(): object => $application,
            static fn(object $application): callable => static fn(): null => null
        );

        $runner->run('config.php');

        $this->assertSame([['config.php', true, $application->errorHandler]], $application->runCalls);
    }

    public function testRunnerRejectsInvalidApplication(): void
    {
        $runner = new request_runner(
            static fn(): object => new stdClass(),
            static fn(): callable => static fn(): null => null
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Request runner requires an application-like object.');

        $runner->run('config.php');
    }

    public function testSourceUsesInjectedApplicationAndErrorHandlerFactories(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/application/request_runner.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('final class request_runner', $source);
        $this->assertStringContainsString('public function __construct(callable $applicationFactory, callable $errorHandlerFactory)', $source);
        $this->assertStringContainsString('$application ??= ($this->applicationFactory)();', $source);
        $this->assertStringNotContainsString('contextBridge', $source);
        $this->assertStringContainsString('return $application->run($configPath, $isEcho, $errorHandler);', $source);
        $this->assertStringNotContainsString('\bootstrap::run', $source);
        $this->assertStringNotContainsString('\bootstrap::setContext(', $source);
        $this->assertStringNotContainsString('new application()', $source);
    }
}

final class RequestRunnerApplicationDouble
{
    public array $runCalls = [];

    public array $events = [];

    public object $context;

    public mixed $errorHandler = null;

    public function __construct()
    {
        $this->context = new stdClass();
    }

    public function run(?string $configPath, bool $isEcho, callable $errorHandler): string
    {
        $this->events[] = 'run';
        $this->errorHandler = $errorHandler;
        $this->runCalls[] = [$configPath, $isEcho, $errorHandler];

        return 'request-result';
    }

    public function context(): object
    {
        $this->events[] = 'context';

        return $this->context;
    }
}
