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
     * @param array<string, array{parameters: list<string>, runtime_arguments: list<string>, container_dependencies: list<string>, optional_arguments: list<string>}> $creatorMethodArguments
     * @param list<string> $aliases
     * @param array{files: list<string>, locations: list<array{file: string, line: int, method?: string, value?: string}>} $referencedBy
     * @param array{aliases: list<string>, classes: list<string>, dependencies: list<string>, factories: list<string>, config_keys: list<string>, runtime_arguments: list<string>, registrar_files: list<string>, creator_methods: list<string>} $sourceEdges
     * @param array{registrations: list<array{file: string, line: int, method?: string, value?: string}>, creator_methods: list<array{file: string, line: int, method?: string, value?: string}>, classes: list<array{file: string, line: int, method?: string, value?: string}>, dependencies: list<array{file: string, line: int, method?: string, value?: string}>, factories: list<array{file: string, line: int, method?: string, value?: string}>, config_keys: list<array{file: string, line: int, method?: string, value?: string}>, runtime_arguments: list<array{file: string, line: int, method?: string, value?: string}>, aliases: list<array{file: string, line: int, method?: string, value?: string}>} $sourceLocations
     */
    public function __construct(
        public readonly string $id,
        public readonly array $registrarFiles,
        public readonly array $creatorMethods,
        public readonly ?bool $shared,
        public readonly array $dependencies,
        public readonly array $factoryOrigin = [],
        public readonly array $factoryArguments = [],
        public readonly array $creatorMethodArguments = [],
        public readonly array $aliases = [],
        public readonly array $referencedBy = [],
        public readonly ?string $class = null,
        public readonly ?string $factory = null,
        public readonly ?string $configKey = null,
        public readonly string $lifetimeReason = '',
        public readonly array $sourceEdges = [],
        public readonly array $sourceLocations = []
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
            'creator_method_arguments' => $this->creatorMethodArguments,
            'aliases' => $this->aliases,
            'referenced_by' => $this->referencedBy,
            'class' => $this->class,
            'factory' => $this->factory,
            'config_key' => $this->configKey,
            'lifetime_reason' => $this->lifetimeReason,
            'source_edges' => $this->sourceEdges,
            'source_locations' => $this->sourceLocations,
        ];

        if ($this->shared !== null) {
            $result['shared'] = $this->shared;
        }

        return $result;
    }
}
