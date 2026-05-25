<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\base\model\entity;


final class model_rowset_factory
{
    private \Closure $configuredServiceFactory;

    public function __construct(
        private object $serializerOperations,
        callable $configuredServiceFactory
    ) {
        $this->configuredServiceFactory = \Closure::fromCallable($configuredServiceFactory);
    }

    public function __invoke(
        string $rowsetClass,
        entity $modelEntity,
        array &$data,
        callable $rowFactory
    ): object {
        return ($this->configuredServiceFactory)($rowsetClass, [
            $modelEntity,
            &$data,
            $rowFactory,
            $this->serializerOperations->phpSnapshotEncoder(),
            $this->serializerOperations->phpSnapshotDecoder()
        ]);
    }

}
