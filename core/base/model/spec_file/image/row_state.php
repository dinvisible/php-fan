<?php

declare(strict_types=1);

namespace fan\core\base\model\spec_file\image;

class row_state
{
    /**
     * @var array<string, object>
     */
    private array $templates = [];

    public function hasTemplate(string $entityName): bool
    {
        return isset($this->templates[$entityName]);
    }

    public function getTemplate(string $entityName): ?object
    {
        return $this->templates[$entityName] ?? null;
    }

    public function setTemplate(string $entityName, object $template): void
    {
        $this->templates[$entityName] = $template;
    }

    /**
     * @return array<string, object>
     */
    public function templates(): array
    {
        return $this->templates;
    }

    public function clear(): void
    {
        $this->templates = [];
    }
}
