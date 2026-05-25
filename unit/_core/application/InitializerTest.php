<?php

declare(strict_types=1);

use fan\core\bootstrap\initializer;
use FanTest\_core\SourceFileContractTestCase;

final class BootstrapInitializerTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/application/initializer.php';

    public function testAdvisedPhpConfigMismatchLogsThroughInjectedRuntime(): void
    {
        $runtime = new BootstrapInitializerRuntimeDouble();
        $settings = new BootstrapInitializerPhpRuntimeSettingsDouble([
            'memory_limit' => 'actual-limit',
        ]);

        new initializer([
            'main_1' => 'date.timezone:UTC',
            'check_adv_1' => 'memory_limit:unlikely-limit',
        ], $runtime, null, null, null, $settings);

        $this->assertSame([['date.timezone', 'UTC']], $settings->sets);
        $this->assertCount(1, $runtime->errors);
        $this->assertStringContainsString('Incorrect value of param "memory_limit = <b>actual-limit</b>"', $runtime->errors[0]);
    }

    public function testMissingDisabledPhpConfigDoesNotLogMismatch(): void
    {
        $runtime = new BootstrapInitializerRuntimeDouble();
        $settings = new BootstrapInitializerPhpRuntimeSettingsDouble();

        new initializer([
            'main_1' => 'date.timezone:UTC',
            'check_adv_1' => 'mbstring.func_overload:0',
        ], $runtime, null, null, null, $settings);

        $this->assertSame([], $runtime->errors);
    }

    public function testSetAppAndServiceParamUseInjectedRuntimeSettings(): void
    {
        $settings = new BootstrapInitializerPhpRuntimeSettingsDouble();
        $initializer = new initializer([
            'main_1' => 'date.timezone:UTC',
            'app_template_1' => 'display_errors:1',
            'service_email_1' => 'sendmail_path:/usr/sbin/sendmail',
        ], new BootstrapInitializerRuntimeDouble(), null, null, null, $settings);

        $this->assertSame([1 => ['display_errors', '1']], $initializer->setAppParam('template'));
        $this->assertSame([1 => ['sendmail_path', '/usr/sbin/sendmail']], $initializer->setServiceParam('email'));
        $this->assertSame([
            ['date.timezone', 'UTC'],
            ['display_errors', '1'],
            ['sendmail_path', '/usr/sbin/sendmail'],
        ], $settings->sets);
    }

    public function testInitAfterLoaderConfiguresCliMatcherFromInjectedInput(): void
    {
        $runtime = new BootstrapInitializerRuntimeDouble(isCli: true);
        $matcher = new BootstrapInitializerMatcherDouble();
        $input = new BootstrapInitializerInputDouble(argv: ['/tmp/jobs/task.php']);
        $initializer = new initializer(
            ['main_1' => 'date.timezone:UTC'],
            $runtime,
            null,
            null,
            static fn(callable $handler): null => null,
            new BootstrapInitializerPhpRuntimeSettingsDouble()
        );

        $this->assertSame($initializer, $initializer->initAfterLoader($matcher, $input));
        $this->assertSame([['task.php', '/tmp/jobs']], $matcher->cliCalls);
    }

    public function testInitAfterLoaderConfiguresHttpMatcherFromInjectedInput(): void
    {
        $runtime = new BootstrapInitializerRuntimeDouble(isCli: false);
        $matcher = new BootstrapInitializerMatcherDouble();
        $input = new BootstrapInitializerInputDouble(server: [
            'HTTP_HOST' => 'example.test',
            'REQUEST_URI' => '/catalog?page=2',
        ]);
        $initializer = new initializer(
            ['main_1' => 'date.timezone:UTC'],
            $runtime,
            null,
            null,
            static fn(callable $handler): null => null,
            new BootstrapInitializerPhpRuntimeSettingsDouble()
        );

        $initializer->initAfterLoader($matcher, $input);

        $this->assertSame([['/catalog?page=2', 'example.test']], $matcher->uriCalls);
    }

    public function testInitAfterLoaderRegistersInjectedRuntimeErrorHandlerThroughDependency(): void
    {
        $runtime = new BootstrapInitializerRuntimeDouble(isCli: true);
        $matcher = new BootstrapInitializerMatcherDouble();
        $input = new BootstrapInitializerInputDouble(argv: ['/tmp/jobs/task.php']);
        $registeredHandler = null;
        $registrar = static function (callable $handler) use (&$registeredHandler): void {
            $registeredHandler = $handler;
        };
        $initializer = new initializer(
            ['main_1' => 'date.timezone:UTC'],
            $runtime,
            null,
            null,
            $registrar,
            new BootstrapInitializerPhpRuntimeSettingsDouble()
        );

        $initializer->initAfterLoader($matcher, $input);

        $this->assertSame([$runtime, 'handleError'], $registeredHandler);
        $this->assertSame([['task.php', '/tmp/jobs']], $matcher->cliCalls);
    }

    public function testSourceNoLongerUsesContainerServiceLocator(): void
    {
        $source = $this->sourceCode();

        $this->assertStringNotContainsString('containerService(', $source);
        $this->assertStringNotContainsString('container_aware_trait', $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/error_handler_registrar.php';", $source);
        $this->assertStringNotContainsString("require_once __DIR__ . '/php_runtime_settings.php';", $source);
        $this->assertStringNotContainsString('new error_handler_registrar()', $source);
        $this->assertStringNotContainsString('new php_runtime_settings()', $source);
        $this->assertStringNotContainsString('defaultErrorHandlerRegistrar', $source);
        $this->assertStringNotContainsString('defaultPhpRuntimeSettings', $source);
        $this->assertStringNotContainsString('set_error_handler([$this->runtime(), \'handleError\']);', $source);
        $this->assertStringNotContainsString("set_error_handler('handleError')", $source);
        $this->assertStringNotContainsString('ini_get(', $source);
        $this->assertStringNotContainsString('ini_set(', $source);
    }
}

final class BootstrapInitializerRuntimeDouble
{
    public array $errors = [];

    public function __construct(private bool $isCli = false)
    {
    }

    public function isCli(): bool
    {
        return $this->isCli;
    }

    public function logError(string $message): void
    {
        $this->errors[] = $message;
    }

    public function handleError(int|float $errNo, string $errMsg, ?string $fileName = null, int|float|null $lineNum = null, mixed $errContext = null): ?bool
    {
        return null;
    }
}

final class BootstrapInitializerInputDouble
{
    public function __construct(
        private array $argv = [],
        private array $server = [],
    ) {
    }

    public function argv(): array
    {
        return $this->argv;
    }

    public function serverValue(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }
}

final class BootstrapInitializerMatcherDouble
{
    public array $cliCalls = [];

    public array $uriCalls = [];

    public function setCli(string $basename, string $dirname): void
    {
        $this->cliCalls[] = [$basename, $dirname];
    }

    public function setUri(string $uri, ?string $host = null): void
    {
        $this->uriCalls[] = [$uri, $host];
    }
}

final class BootstrapInitializerPhpRuntimeSettingsDouble
{
    public array $sets = [];

    public function __construct(private array $values = [])
    {
    }

    public function get(string $name): string|false
    {
        return $this->values[$name] ?? false;
    }

    public function set(string $name, string $value): string|false
    {
        $this->sets[] = [$name, $value];
        $previous = $this->values[$name] ?? false;
        $this->values[$name] = $value;

        return $previous;
    }
}
