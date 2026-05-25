<?php

declare(strict_types=1);

use fan\core\service\tab\delegate;
use FanTest\_core\SourceFileContractTestCase;
use fan\core\base\service;


class ServiceTabDelegateTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/service/tab/delegate.php';

    public function testSetFacadeStoresFacadeConfig(): void
    {
        $config = new ServiceTabDelegateConfigDouble([
            'default_ext' => 'html',
        ]);
        $facade = new ServiceTabDelegateFacadeDouble($config);
        $delegate = new ServiceTabDelegateProbe();

        $this->assertSame($delegate, $delegate->setFacade($facade));
        $this->assertSame($config, $delegate->getConfig());
        $this->assertSame('html', $delegate->getConfig('default_ext'));
        $this->assertSame('fallback', $delegate->getConfig('missing', 'fallback'));
    }
}

final class ServiceTabDelegateProbe extends delegate
{
    public function __construct()
    {
    }
}

final class ServiceTabDelegateFacadeDouble extends service
{
    public function __construct(private ServiceTabDelegateConfigDouble $configDouble)
    {
    }

    public function isSingleton(): bool
    {
        return false;
    }

    public function getConfig($key = null, $default = null): mixed
    {
        if ($key === null) {
            return $this->configDouble;
        }

        return $this->configDouble->get($key, $default);
    }
}

final class ServiceTabDelegateConfigDouble
{
    public function __construct(private array $data = [])
    {
    }

    public function get(mixed $key = null, mixed $default = null): mixed
    {
        return $key === null ? $this->data : ($this->data[$key] ?? $default);
    }
}
