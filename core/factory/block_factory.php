<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\block\base;


final class block_factory
{
    private \Closure $configuredServiceFactory;

    public function __construct(callable $configuredServiceFactory)
    {
        $this->configuredServiceFactory = \Closure::fromCallable($configuredServiceFactory);
    }

    public function __invoke(
        string $blockClass,
        string $blockName,
        object $tabService,
        ?base $containerBlock,
        array $meta,
        bool $allowMeta,
        mixed $inBranch,
        array $dependencies
    ): mixed {
        return ($this->configuredServiceFactory)($blockClass, [
            $blockName,
            $tabService,
            $containerBlock,
            $meta,
            $allowMeta,
            null,
            $dependencies
        ]);
    }
}
