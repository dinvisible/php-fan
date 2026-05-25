<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\base\model\entity;
use fan\core\base\model\rowset;


final class model_row_factory
{
    private \Closure $configuredServiceFactory;

    private \Closure $modelRowExceptionFactory;

    public function __construct(
        private object $serializerOperations,
        callable $configuredServiceFactory,
        callable $modelRowExceptionFactory
    ) {
        $this->configuredServiceFactory = \Closure::fromCallable($configuredServiceFactory);
        $this->modelRowExceptionFactory = \Closure::fromCallable($modelRowExceptionFactory);
    }

    public function __invoke(
        string $rowClass,
        entity $modelEntity,
        array &$data = [],
        ?rowset $rowset = null
    ): object {
        return ($this->configuredServiceFactory)($rowClass, [
            $modelEntity,
            &$data,
            $rowset,
            null,
            $this->serializerOperations->phpSnapshotEncoder(),
            $this->serializerOperations->phpSnapshotDecoder(),
            $this->modelRowExceptionFactory
        ]);
    }

}
