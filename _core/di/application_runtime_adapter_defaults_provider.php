<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_runtime_adapter_defaults_provider
{
    private \Closure $dataLoaderFactory;

    private \Closure $errorLogWriterFactory;

    private \Closure $headerWriterFactory;

    private \Closure $cookieWriterFactory;

    private \Closure $curlAdapterFactory;

    public function __construct(
        callable $dataLoaderFactory,
        callable $errorLogWriterFactory,
        callable $headerWriterFactory,
        callable $cookieWriterFactory,
        callable $curlAdapterFactory
    )
    {
        $this->dataLoaderFactory = \Closure::fromCallable($dataLoaderFactory);
        $this->errorLogWriterFactory = \Closure::fromCallable($errorLogWriterFactory);
        $this->headerWriterFactory = \Closure::fromCallable($headerWriterFactory);
        $this->cookieWriterFactory = \Closure::fromCallable($cookieWriterFactory);
        $this->curlAdapterFactory = \Closure::fromCallable($curlAdapterFactory);
    }

    public function dataLoaderFactory(): callable
    {
        return $this->dataLoaderFactory;
    }

    public function errorLogWriterFactory(): callable
    {
        return $this->errorLogWriterFactory;
    }

    public function headerWriterFactory(): callable
    {
        return $this->headerWriterFactory;
    }

    public function cookieWriterFactory(): callable
    {
        return $this->cookieWriterFactory;
    }

    public function curlAdapterFactory(): callable
    {
        return $this->curlAdapterFactory;
    }
}
