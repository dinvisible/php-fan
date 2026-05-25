<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\block\base;


final class block_exception_factory
{
    private \Closure $configuredServiceFactory;

    public function __construct(
        callable $configuredServiceFactory,
        private ?object $exceptionDatabaseConnections = null,
        private ?object $exceptionRuntimeLogger = null,
        private ?object $exceptionRequestService = null,
        private ?object $exceptionErrorService = null,
        private ?object $exceptionHeaderWriter = null
    ) {
        $this->configuredServiceFactory = \Closure::fromCallable($configuredServiceFactory);
    }

    public function setExceptionDependencies(
        ?object $exceptionDatabaseConnections = null,
        ?object $exceptionRuntimeLogger = null,
        ?object $exceptionRequestService = null,
        ?object $exceptionErrorService = null,
        ?object $exceptionHeaderWriter = null
    ): self {
        $this->exceptionDatabaseConnections = $exceptionDatabaseConnections;
        $this->exceptionRuntimeLogger = $exceptionRuntimeLogger;
        $this->exceptionRequestService = $exceptionRequestService;
        $this->exceptionErrorService = $exceptionErrorService;
        $this->exceptionHeaderWriter = $exceptionHeaderWriter;

        return $this;
    }

    public function __invoke(
        string $exceptionClass,
        base $block,
        string $message,
        int $code,
        ?\Exception $previous = null
    ): \Throwable {
        return ($this->configuredServiceFactory)($exceptionClass, [
            $block,
            $message,
            $code,
            $previous,
            $this->exceptionDatabaseConnections,
            $this->exceptionRuntimeLogger,
            $this->exceptionRequestService,
            $this->exceptionErrorService,
            $this->exceptionHeaderWriter
        ]);
    }
}
