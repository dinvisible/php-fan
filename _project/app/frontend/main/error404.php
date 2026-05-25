<?php

declare(strict_types=1);

namespace fan\app\frontend\main;
use fan\project\block\error\error404 as error_error404;

/**
 * Error404 class
 * @version 05.02.001 (10.03.2014)
 */
class error404 extends error_error404
{
    public function init(): void
    {
        $this->setViewVars('Error 404', 'Requested page is not found.', 'Error 404. Requested page isn\'t found.');
    }
}
