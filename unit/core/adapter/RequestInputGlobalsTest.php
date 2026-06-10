<?php

declare(strict_types=1);

use fan\core\adapter\request_input_globals;
use FanTest\core\SourceFileContractTestCase;

final class AdapterRequestInputGlobalsTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/adapter/request_input_globals.php';

    public function testDelegatesGlobalArraysAndServerValuesToInjectedEnvironment(): void
    {
        $environment = new RequestInputEnvironmentDouble(
            arrays: ['_GET' => ['page' => '2']],
            server: ['HTTP_HOST' => 'example.test']
        );

        $source = new request_input_globals($environment);

        $this->assertSame(['page' => '2'], $source->globalArray('_GET'));
        $this->assertSame('example.test', $source->serverValue('HTTP_HOST'));
        $this->assertSame('fallback', $source->serverValue('MISSING', 'fallback'));
    }

    public function testDelegatesMutationsAndSessionRootToInjectedEnvironment(): void
    {
        $environment = new RequestInputEnvironmentDouble(
            arrays: ['_GET' => ['remove' => 'yes', 'keep' => 'ok']]
        );

        $source = new request_input_globals($environment);
        $source->unsetGlobalValue('_GET', 'remove');
        $session =& $source->sessionRoot();
        $session['token'] = 'abc';

        $this->assertSame(['keep' => 'ok'], $environment->arrays['_GET']);
        $this->assertSame(['token' => 'abc'], $environment->session);
    }

    public function testDelegatesHeadersAndRawPostToInjectedEnvironment(): void
    {
        $environment = new RequestInputEnvironmentDouble(headers: ['Host' => 'example.test'], rawPost: 'payload');
        $source = new request_input_globals($environment);

        $this->assertSame(['Host' => 'example.test'], $source->apacheHeaders());
        $this->assertSame('payload', $source->rawPost());
    }

    public function testSourceUsesInjectedEnvironment(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/adapter/request_input_globals.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('__construct(private object $environment)', $source);
        $this->assertStringContainsString('$this->environment->globalArray($name)', $source);
        $this->assertStringNotContainsString('$GLOBALS[', $source);
        $this->assertStringNotContainsString('$_SERVER[', $source);
        $this->assertStringNotContainsString('apache_request_headers()', $source);
        $this->assertStringNotContainsString('file_get_contents(\'php://input\')', $source);
    }
}

final class RequestInputEnvironmentDouble
{
    public array $session = [];

    public function __construct(
        public array $arrays = [],
        private array $values = [],
        private array $server = [],
        private ?array $headers = null,
        private string $rawPost = ''
    ) {
    }

    public function globalArray(string $name): array
    {
        return $this->arrays[$name] ?? [];
    }

    public function globalValue(string $name, mixed $default = null): mixed
    {
        return $this->values[$name] ?? $default;
    }

    public function unsetGlobalValue(string $name, mixed $key): void
    {
        unset($this->arrays[$name][$key]);
    }

    public function serverValue(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }

    public function &sessionRoot(): array
    {
        return $this->session;
    }

    public function apacheHeaders(): ?array
    {
        return $this->headers;
    }

    public function rawPost(): string
    {
        return $this->rawPost;
    }
}
