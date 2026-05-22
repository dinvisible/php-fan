<?php

declare(strict_types=1);

namespace fan\app\__log_viewer\main;
/**
 * Error 404 class
 * @version 05.02.001 (10.03.2014)
 */
class error404 extends \fan\project\block\error\error404
{
    public function init(): void
    {
        $this->setViewVars('Error 404', 'Such page is\'t available.', 'Page is\'t available.');
    }
}
