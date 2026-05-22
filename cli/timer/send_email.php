<?php

declare(strict_types=1);

namespace fan\project\cli\timer;
/**
 * Timer manager service
 * @version 05.02.007 (31.08.2015)
 */
class send_email extends \fan\core\base\timer_program
{

    public function sendEmail($subject, $message, $mailTo, $nameTo, $mailCC): void
    {
        $servEmail = service('email', "timer_email");
        $servEmail->clear_all_recipients();
        if ($mailCC) {
            foreach ($mailCC as $v) {
                list($email, $name) = explode("/", $v, 2);
                $servEmail->add_cc($email, $name);
            }
        }
        $servEmail->send($subject, $message, $mailTo, $nameTo);

    }

}
