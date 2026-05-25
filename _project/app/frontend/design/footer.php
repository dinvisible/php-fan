<?php

declare(strict_types=1);

namespace fan\app\frontend\design;
use fan\project\block\common\simple;

/**
 * footer class
 * @version 05.02.003 (16.04.2014)
 */
class footer extends simple
{
    public function init(): void
    {
        $this->view->cyear = date('Y');
    }

}
