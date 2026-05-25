<?php

declare(strict_types=1);

use fan\core\di\timer_program_factory;
use PHPUnit\Framework\TestCase;

final class TimerProgramFactoryTest extends TestCase
{
    public function testFactoryDelegatesRequestedTimerProgramClassToConfiguredFactory(): void
    {
        $delegatedClass = null;
        $delegatedArguments = null;
        $factory = new timer_program_factory(
            static function (string $className, array $arguments) use (&$delegatedClass, &$delegatedArguments): object {
                $delegatedClass = $className;
                $delegatedArguments = $arguments;

                return new $className(...$arguments);
            }
        );
        $program = $factory(TimerProgramFactoryProgramDouble::class);

        $this->assertSame(TimerProgramFactoryProgramDouble::class, $delegatedClass);
        $this->assertSame([], $delegatedArguments);
        $this->assertInstanceOf(TimerProgramFactoryProgramDouble::class, $program);
    }}

final class TimerProgramFactoryProgramDouble
{
}
