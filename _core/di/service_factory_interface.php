<?php

declare(strict_types=1);

namespace fan\core\di;

interface service_factory_interface
{
    public function has(string $serviceName): bool;

    public function create(string $serviceName, array $arguments = []): mixed;
}
