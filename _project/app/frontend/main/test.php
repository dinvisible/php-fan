<?php

declare(strict_types=1);

namespace fan\app\frontend\main;
use fan\project\block\common\simple;

/**
 * Test class for the frontend test page.
 * @version 05.02.001 (10.03.2014)
 */
class test extends simple
{
    public function init(): void
    {
        $this->view->content = 'content';
    }

}
