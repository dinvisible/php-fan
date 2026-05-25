<?php

declare(strict_types=1);

namespace fan\app\frontend\main;
use fan\project\block\common\simple;

/**
 * Test class index
 * @version 05.02.001 (10.03.2014)
 */
class index extends simple
{
    public function init(): void
    {
        $this->view->hello = 'lslsHello world!';
    }

}
