<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../_core/service/header/code.php';

class ServiceHeaderCodeTest extends \PHPUnit\Framework\TestCase
{
    public function testCodeGroupsExposeExpectedStatusTexts(): void
    {
        $this->assertSame('Continue', \fan\core\service\header\code::getCodes1()[100]);
        $this->assertSame('OK', \fan\core\service\header\code::getCodes2()[200]);
        $this->assertSame('Moved Permanently', \fan\core\service\header\code::getCodes3()[301]);
        $this->assertSame('Not Found', \fan\core\service\header\code::getCodes4()[404]);
        $this->assertSame('Internal Server Error', \fan\core\service\header\code::getCodes5()[500]);
    }

    public function testCodeGroupsRemainSeparatedByStatusClass(): void
    {
        $groups = [
            1 => \fan\core\service\header\code::getCodes1(),
            2 => \fan\core\service\header\code::getCodes2(),
            3 => \fan\core\service\header\code::getCodes3(),
            4 => \fan\core\service\header\code::getCodes4(),
            5 => \fan\core\service\header\code::getCodes5(),
        ];

        foreach ($groups as $class => $codes) {
            $this->assertNotSame([], $codes);
            foreach (array_keys($codes) as $code) {
                $this->assertSame($class, intdiv($code, 100));
            }
        }
    }
}
