<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\view\keeper\loader\json;
use fan\core\view\router\loader;
use fan\project\view\keeper\loader\json as loader_json;


final class view_loader_json_keeper_factory
{
    public function __invoke(loader $router): json
    {
        return new loader_json($router);
    }
}
