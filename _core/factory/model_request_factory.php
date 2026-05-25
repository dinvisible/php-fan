<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\base\model\entity;


final class model_request_factory
{
    private \Closure $configuredServiceFactory;

    public function __construct(callable $configuredServiceFactory, private object $fileStorage)
    {
        $this->configuredServiceFactory = \Closure::fromCallable($configuredServiceFactory);
    }

    public function __invoke(
        string $requestClass,
        entity $modelEntity,
        object $reflector
    ): object {
        return ($this->configuredServiceFactory)($requestClass, [$modelEntity, $reflector, $this->fileStorage]);
    }

}
