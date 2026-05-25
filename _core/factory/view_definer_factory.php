<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\project\view\definer;


final class view_definer_factory
{
    public function __invoke(array $config, object $request, object $tab): object
    {
        return new definer($config, $request, $tab);
    }
}
