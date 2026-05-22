<?php

declare(strict_types=1);

namespace fan\app\frontend\main;
/**
 * Test class for the frontend test page.
 * @version 05.02.001 (10.03.2014)
 */
class test extends \fan\project\block\common\simple
{
    public function init(): void
    {
        $this->view->content = 'content';
    }

}
