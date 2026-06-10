<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\view\keeper\loader\text;
use fan\core\view\router\loader;
use fan\project\view\keeper\loader\text as loader_text;


final class view_loader_text_keeper_factory
{
    public function __invoke(loader $router): text
    {
        return new loader_text($router);
    }
}
