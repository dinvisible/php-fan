<?php

declare(strict_types=1);

namespace fan\project\cli\timer;
/**
 * Timer send packet of error email
 * @version 05.02.007 (31.08.2015)
 */
class error_email extends \fan\core\base\timer_program
{
    public function sendPacketEmais(): void
    {
        $this->containerService('error')->sendPacketEmais();
    }

}
