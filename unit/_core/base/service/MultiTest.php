<?php

declare(strict_types=1);

use fan\core\base\service\multi;
use FanTest\_core\SourceFileContractTestCase;

class BaseServiceMultiTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = '_core/base/service/multi.php';

    public function testMultiServiceIsNotSingleton(): void
    {
        $this->assertFalse((new BaseServiceMultiProbe())->isSingleton());
    }

    public function testResetEnabledAllCallsEveryPassedService(): void
    {
        $first = new BaseServiceMultiResetDouble();
        $second = new BaseServiceMultiResetDouble();

        BaseServiceMultiProbe::resetEnabledAll([$first, $second]);

        $this->assertSame(1, $first->resetCalls);
        $this->assertSame(1, $second->resetCalls);
    }
}

final class BaseServiceMultiProbe extends multi
{
    public function __construct()
    {
    }
}

final class BaseServiceMultiResetDouble
{
    public int $resetCalls = 0;

    public function resetEnabled(): void
    {
        $this->resetCalls++;
    }
}
