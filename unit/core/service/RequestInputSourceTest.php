<?php

declare(strict_types=1);

use fan\core\service\request_input_source;
use FanTest\core\SourceFileContractTestCase;

final class ServiceRequestInputSourceTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/service/request_input_source.php';

    public function testDelegatesEnvironmentAccessToInjectedAdapter(): void
    {
        $environment = new RequestInputSourceEnvironmentDouble();
        $source = new request_input_source($environment);

        $this->assertSame(['page' => '2'], $source->globalArray('_GET'));
        $this->assertSame('5.22', $source->globalValue('TEST_GLOBAL_VALUE'));
        $this->assertSame('example.test', $source->serverValue('HTTP_HOST'));
        $this->assertSame(['Host' => 'example.test'], $source->apacheHeaders());
        $this->assertSame('payload', $source->rawPost());
        $source->unsetGlobalValue('_GET', 'remove');
        $session =& $source->sessionRoot();
        $session['token'] = 'abc';

        $this->assertSame([['_GET', 'remove']], $environment->unsetCalls);
        $this->assertSame(['token' => 'abc'], $environment->session);
    }

    public function testSourceNoLongerReadsRawEnvironmentDirectly(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/core/service/request_input_source.php');

        $this->assertIsString($source);
        $this->assertStringContainsString('__construct(private object $environment)', $source);
        $this->assertStringNotContainsString('$GLOBALS[', $source);
        $this->assertStringNotContainsString('$_SERVER[', $source);
        $this->assertStringNotContainsString('file_get_contents(', $source);
        $this->assertStringNotContainsString('apache_request_headers(', $source);
    }
}

final class RequestInputSourceEnvironmentDouble
{
    public array $session = [];
    public array $unsetCalls = [];

    public function globalArray(string $name): array
    {
        return $name === '_GET' ? ['page' => '2'] : [];
    }

    public function globalValue(string $name, mixed $default = null): mixed
    {
        return $name === 'TEST_GLOBAL_VALUE' ? '5.22' : $default;
    }

    public function unsetGlobalValue(string $name, mixed $key): void
    {
        $this->unsetCalls[] = [$name, $key];
    }

    public function serverValue(string $key, mixed $default = null): mixed
    {
        return $key === 'HTTP_HOST' ? 'example.test' : $default;
    }

    public function &sessionRoot(): array
    {
        return $this->session;
    }

    public function apacheHeaders(): ?array
    {
        return ['Host' => 'example.test'];
    }

    public function rawPost(): string
    {
        return 'payload';
    }
}
