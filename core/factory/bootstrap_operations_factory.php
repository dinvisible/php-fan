<?php

declare(strict_types=1);

namespace fan\core\bootstrap;

final class bootstrap_operations_factory
{
    private object $operations;

    public function __construct(object $operations)
    {
        $this->operations = $operations;
    }

    public function __invoke(): array
    {
        return [
            'getLoader' => $this->operation('getLoader'),
            'getRunner' => $this->operation('getRunner'),
            'getInitializer' => $this->operation('getInitializer'),
            'parsePath' => $this->operation('parsePath'),
            'loadClass' => $this->operation('loadClass'),
            'logError' => $this->operation('logError'),
            'handleError' => $this->operation('handleError'),
            'getGlobalPath' => $this->operation('getGlobalPath'),
            'getConfigCache' => $this->operation('getConfigCache'),
            'getPid' => $this->operation('getPid'),
            'isCli' => $this->operation('isCli'),
        ];
    }

    private function operation(string $name): callable
    {
        $operation = [$this->operations, $name];
        if (!is_callable($operation)) {
            throw new \RuntimeException('Bootstrap operations object must provide ' . $name . '().');
        }

        return $operation;
    }

}
