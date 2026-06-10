<?php

declare(strict_types=1);

use fan\core\base\timer_program;
use FanTest\core\SourceFileContractTestCase;

class BaseTimerProgramTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/base/timer_program.php';

    public function testTimerRowCanBeStoredAndReturned(): void
    {
        $program = new BaseTimerProgramProbe();
        $row = new BaseTimerProgramRowDouble(15);

        $program->setTimerRow($row);

        $this->assertSame($row, $program->getTimerRow());
    }

    public function testPeriodFallsBackToTimerRowUntilExplicitNonNegativePeriodIsSet(): void
    {
        $program = new BaseTimerProgramProbe();
        $program->setTimerRow(new BaseTimerProgramRowDouble(15));

        $this->assertSame(15, $program->getPeriod());

        $program->setPeriod(-1);
        $this->assertSame(15, $program->getPeriod());

        $program->setPeriod(30);
        $this->assertSame(30, $program->getPeriod());
    }
}

final class BaseTimerProgramProbe extends timer_program
{
}

final class BaseTimerProgramRowDouble
{
    public function __construct(private int|float $period)
    {
    }

    public function get_period(int $default, bool $withDefault): int|float
    {
        return $this->period;
    }
}
