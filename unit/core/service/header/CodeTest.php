<?php

declare(strict_types=1);
use fan\core\service\header\code;
use PHPUnit\Framework\TestCase;


require_once __DIR__ . '/../../../../core/service/header/code.php';

class ServiceHeaderCodeTest extends TestCase
{
    public function testCodeGroupsExposeExpectedStatusTexts(): void
    {
        $this->assertSame('Continue', code::getCodes1()[100]);
        $this->assertSame('OK', code::getCodes2()[200]);
        $this->assertSame('Moved Permanently', code::getCodes3()[301]);
        $this->assertSame('Not Found', code::getCodes4()[404]);
        $this->assertSame('Internal Server Error', code::getCodes5()[500]);
    }

    public function testCodeGroupsRemainSeparatedByStatusClass(): void
    {
        $groups = [
            1 => code::getCodes1(),
            2 => code::getCodes2(),
            3 => code::getCodes3(),
            4 => code::getCodes4(),
            5 => code::getCodes5(),
        ];

        foreach ($groups as $class => $codes) {
            $this->assertNotSame([], $codes);
            foreach (array_keys($codes) as $code) {
                $this->assertSame($class, intdiv($code, 100));
            }
        }
    }
}
