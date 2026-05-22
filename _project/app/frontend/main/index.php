<?php

declare(strict_types=1);

namespace fan\app\frontend\main;
/**
 * Test class index
 * @version 05.02.001 (10.03.2014)
 */
class index extends \fan\project\block\common\simple
{
    public function init(): void
    {
        $this->view->hello = 'lslsHello world!';
    }

}
