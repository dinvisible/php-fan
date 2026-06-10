<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\project\exception\error500;


final class error500_exception_factory
{
    public function __construct(
        private ?object $exceptionDatabaseConnections = null,
        private ?object $exceptionRuntimeLogger = null,
        private ?object $exceptionRequestService = null,
        private ?object $exceptionErrorService = null,
        private ?object $exceptionHeaderWriter = null
    ) {
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
        string $message,
        int $code = E_USER_ERROR,
        ?\Throwable $previous = null
    ): \Throwable {
        return new error500(
            $message,
            $code,
            $previous,
            $this->exceptionDatabaseConnections,
            $this->exceptionRuntimeLogger,
            $this->exceptionRequestService,
            $this->exceptionErrorService,
            $this->exceptionHeaderWriter
        );
    }
}
