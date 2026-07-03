<?php

declare(strict_types=1);

use fan\core\bootstrap\runner;
use FanTest\core\SourceFileContractTestCase;

final class BootstrapRunnerTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/application/runner.php';

    public function testGetHandlerResolvesServiceProcedureThroughInjectedHandlerMap(): void
    {
        $service = new BootstrapRunnerServiceDouble();
        $runner = new runner(
            [],
            self::phpArrayFileLoader(),
            self::errorDemonstratorFactory(),
            new BootstrapRunnerMatcherDouble([
                'service' => 'tab',
                'method' => 'handleContent',
                'param' => ['alpha'],
            ]),
            handlerFactories: [
                'tab' => static fn(): object => $service,
            ]
        );

        [$procedure, $parameters] = $runner->getHandler();

        $this->assertSame([$service, 'handleContent'], $procedure);
        $this->assertSame(['alpha'], $parameters);
    }

    public function testSourceUsesInjectedHandlerFactoryDirectly(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('$factory = $this->handlerFactories[$serviceName];', $source);
        $this->assertStringContainsString('return $this->requireDependency($factory(),', $source);
        $this->assertStringNotContainsString('call_user_func($this->handlerFactories[$serviceName])', $source);
    }

    public function testGetHandlerRejectsUnconfiguredHandlerService(): void
    {
        $runner = new runner(
            [],
            self::phpArrayFileLoader(),
            self::errorDemonstratorFactory(),
            new BootstrapRunnerMatcherDouble([
                'service' => 'unknown',
                'method' => 'handleContent',
                'param' => [],
            ])
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Handler service "unknown" is not configured for bootstrap runner.');

        $runner->getHandler();
    }

    public function testRunSendsHeadersAndEchoesScalarResult(): void
    {
        $header = new BootstrapRunnerHeaderDouble();
        $runner = new runner([], self::phpArrayFileLoader(), self::errorDemonstratorFactory(), header: $header);
        $procedure = new BootstrapRunnerProcedureDouble();

        ob_start();
        $result = $runner->run(true, [$procedure, 'payload']);
        $output = ob_get_clean();

        $this->assertSame('payload', $result);
        $this->assertSame('payload', $output);
        $this->assertTrue($header->sent);
    }

    public function testRunHandlesTypeErrorsAsServerErrors(): void
    {
        $runtime = new BootstrapRunnerRuntimeDouble();
        $input = new BootstrapRunnerInputDouble();
        $runner = new runner(
            [],
            self::phpArrayFileLoader(),
            self::errorDemonstratorFactory(),
            input: $input,
            runtime: $runtime
        );
        $procedure = new BootstrapRunnerProcedureDouble();

        $result = $runner->run(false, [$procedure, 'brokenType']);

        $this->assertNull($result);
        $this->assertCount(1, $runtime->messages);
        $this->assertStringContainsString('TypeError', $runtime->messages[0]);
        $this->assertStringContainsString('broken type', $runtime->messages[0]);
    }

    public function testLogExceptionUsesInjectedRuntime(): void
    {
        $runtime = new BootstrapRunnerRuntimeDouble();
        $runner = new BootstrapRunnerProbe([], self::phpArrayFileLoader(), self::errorDemonstratorFactory(), runtime: $runtime);

        $runner->exposeLogException(new RuntimeException('broken'));

        $this->assertCount(1, $runtime->messages);
        $this->assertStringContainsString('Uncaught exception "RuntimeException"', $runtime->messages[0]);
        $this->assertStringContainsString('broken', $runtime->messages[0]);
    }

    public function testSourcePassesInjectedPhpArrayLoaderToErrorDemonstrator(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('callable $phpArrayFileLoader', $source);
        $this->assertStringContainsString('callable $errorDemonstratorFactory', $source);
        $this->assertStringContainsString('\Closure::fromCallable($phpArrayFileLoader)', $source);
        $this->assertStringContainsString('\Closure::fromCallable($errorDemonstratorFactory)', $source);
        $this->assertStringContainsString('$this->errorDemonstratorFactory()', $source);
        $this->assertStringContainsString('private function phpArrayFileLoader(): callable', $source);
        $this->assertStringContainsString('private function errorDemonstratorFactory(): callable', $source);
        $this->assertStringNotContainsString('defaultErrorDemonstratorFactory', $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/error_demonstrator_factory.php';", $source);
        $this->assertStringNotContainsString('new error_demonstrator_factory()', $source);
        $this->assertStringNotContainsString('new \fan\core\error\demonstrator', $source);
        $this->assertStringNotContainsString('new \fan\project\error\demonstrator', $source);
    }

    public function testShowErrorUsesInjectedErrorDemonstratorFactory(): void
    {
        $input = new BootstrapRunnerInputDouble();
        $phpArrayFileLoader = static fn(string $path, mixed $default = null): mixed => $default;
        $calls = [];
        $demonstrator = new BootstrapRunnerErrorDemonstratorDouble();
        $runner = new runner(
            [],
            $phpArrayFileLoader,
            static function (
                array $errMsg,
                string $tplName,
                object $receivedInput,
                callable $receivedPhpArrayFileLoader
            ) use (&$calls, $input, $phpArrayFileLoader, $demonstrator): object {
                $calls[] = [$errMsg, $tplName, $receivedInput, $receivedPhpArrayFileLoader];

                return $demonstrator;
            },
            input: $input
        );

        $this->assertSame('error-content', $runner->showError(['broken'], 'error_500', false));
        $this->assertCount(1, $calls);
        $this->assertSame(['broken'], $calls[0][0]);
        $this->assertSame('error_500', $calls[0][1]);
        $this->assertSame($input, $calls[0][2]);
        $this->assertSame($phpArrayFileLoader, $calls[0][3]);
    }

    public function testSourceUsesNativeCallableInvocationForProcedures(): void
    {
        $source = $this->sourceCode();

        $this->assertStringContainsString('$ret = $procedure(...(empty($parameters) ? [] : $parameters));', $source);
        $this->assertStringContainsString('$ret();', $source);
        $this->assertStringNotContainsString('public function runCli', $source);
        $this->assertStringNotContainsString('call_user_func_array($procedure', $source);
        $this->assertStringNotContainsString('call_user_func($ret)', $source);
    }

    private static function phpArrayFileLoader(): callable
    {
        return static fn(string $path, mixed $default = null): mixed => $default;
    }

    private static function errorDemonstratorFactory(): callable
    {
        return static fn(array $errMsg, string $tplName, object $input, callable $phpArrayFileLoader): object => new BootstrapRunnerErrorDemonstratorDouble();
    }
}

final class BootstrapRunnerProbe extends runner
{
    public function exposeLogException(Throwable $exception): void
    {
        $this->_logException($exception);
    }
}

final class BootstrapRunnerMatcherDouble
{
    public function __construct(private array $handler)
    {
    }

    public function getCurrentHandler(): array
    {
        return $this->handler;
    }
}

final class BootstrapRunnerServiceDouble
{
    public function handleContent(string $value): string
    {
        return $value;
    }
}

final class BootstrapRunnerHeaderDouble
{
    public bool $sent = false;

    public function sendHeaders(): void
    {
        $this->sent = true;
    }
}

final class BootstrapRunnerProcedureDouble
{
    public function payload(): string
    {
        return 'payload';
    }

    public function brokenType(): never
    {
        throw new TypeError('broken type');
    }
}

final class BootstrapRunnerRuntimeDouble
{
    public array $messages = [];

    public function logError(string $message): void
    {
        $this->messages[] = $message;
    }
}

final class BootstrapRunnerInputDouble
{
    public function serverValue(string $key, mixed $default = null): mixed
    {
        return $default;
    }
}

final class BootstrapRunnerErrorDemonstratorDouble
{
    public function showTplContent(): string
    {
        return 'shown-error-content';
    }

    public function getTplContent(): string
    {
        return 'error-content';
    }
}
