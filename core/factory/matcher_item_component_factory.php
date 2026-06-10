<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\service\matcher\item;


final class matcher_item_component_factory
{
    private \Closure $configuredServiceFactory;

    public function __construct(callable $configuredServiceFactory)
    {
        $this->configuredServiceFactory = \Closure::fromCallable($configuredServiceFactory);
    }

    public function __invoke(string $componentClassName, item $item): object
    {
        return ($this->configuredServiceFactory)($componentClassName, [$item]);
    }

}
