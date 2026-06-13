<?php

declare(strict_types=1);

namespace fan\core\di;

use fan\core\adapter\reflection_class_factory;

final class bootstrap_object_class_instantiator_provider
{
    public function __invoke(): callable
    {
        return new configured_class_instantiator(new reflection_class_factory());
    }
}
