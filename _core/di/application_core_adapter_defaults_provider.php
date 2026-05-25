<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_core_adapter_defaults_provider
{
    private \Closure $serializerOperationsFactory;

    public function __construct(
        private object $warningCapture,
        private object $phpArrayFileLoader,
        callable $serializerOperationsFactory
    )
    {
        $this->serializerOperationsFactory = \Closure::fromCallable($serializerOperationsFactory);
    }

    public function warningCapture(): object
    {
        return $this->warningCapture;
    }

    public function phpArrayFileLoader(): callable
    {
        return $this->phpArrayFileLoader;
    }

    public function serializerOperationsFactory(): callable
    {
        return $this->serializerOperationsFactory;
    }
}
