<?php

declare(strict_types=1);

namespace fan\core\di;

final class service_descriptor
{
    /**
     * @param list<string> $registrarFiles
     * @param list<string> $creatorMethods
     * @param list<string> $dependencies
     * @param array{registrar_files: list<string>, creator_methods: list<string>} $factoryOrigin
     * @param array{container_dependencies: list<string>, runtime_arguments: list<string>} $factoryArguments
     * @param list<string> $aliases
     */
    public function __construct(
        public readonly string $id,
        public readonly array $registrarFiles,
        public readonly array $creatorMethods,
        public readonly ?bool $shared,
        public readonly array $dependencies,
        public readonly array $factoryOrigin = [],
        public readonly array $factoryArguments = [],
        public readonly array $aliases = []
    )
    {
    }

    public function toArray(): array
    {
        $result = [
            'id' => $this->id,
            'registrar_files' => $this->registrarFiles,
            'creator_methods' => $this->creatorMethods,
            'dependencies' => $this->dependencies,
            'factory_origin' => $this->factoryOrigin,
            'factory_arguments' => $this->factoryArguments,
            'aliases' => $this->aliases,
        ];

        if ($this->shared !== null) {
            $result['shared'] = $this->shared;
        }

        return $result;
    }
}
