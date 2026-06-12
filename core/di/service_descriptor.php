<?php

declare(strict_types=1);

namespace fan\core\di;

final class service_descriptor
{
    /**
     * @param list<string> $registrarFiles
     * @param list<string> $creatorMethods
     * @param list<string> $dependencies
     */
    public function __construct(
        public readonly string $id,
        public readonly array $registrarFiles,
        public readonly array $creatorMethods,
        public readonly ?bool $shared,
        public readonly array $dependencies
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
        ];

        if ($this->shared !== null) {
            $result['shared'] = $this->shared;
        }

        return $result;
    }
}
