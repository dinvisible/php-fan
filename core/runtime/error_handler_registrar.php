<?php

declare(strict_types=1);

namespace fan\core\runtime;

final class error_handler_registrar
{
    public function __invoke(callable $handler): mixed
    {
        return set_error_handler($handler);
    }
}
