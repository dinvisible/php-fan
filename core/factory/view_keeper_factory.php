<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\view\keeper;
use fan\core\view\router;
use fan\project\view\keeper as view_keeper;


final class view_keeper_factory
{
    public function __invoke(router $router): keeper
    {
        return new view_keeper($router);
    }
}
