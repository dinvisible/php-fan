<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\project\exception\plain\fatal;


final class plain_exception_factory
{
    public function __construct(
        private ?object $exceptionDatabaseConnections = null,
        private ?object $exceptionRuntimeLogger = null,
        private ?object $exceptionRequestService = null,
        private ?object $exceptionErrorService = null,
        private ?object $exceptionHeaderWriter = null,
        private mixed $classNameResolver = null
    ) {
    }

    public function setExceptionDependencies(
        ?object $exceptionDatabaseConnections = null,
        ?object $exceptionRuntimeLogger = null,
        ?object $exceptionRequestService = null,
        ?object $exceptionErrorService = null,
        ?object $exceptionHeaderWriter = null,
        ?callable $classNameResolver = null
    ): self {
        $this->exceptionDatabaseConnections = $exceptionDatabaseConnections;
        $this->exceptionRuntimeLogger = $exceptionRuntimeLogger;
        $this->exceptionRequestService = $exceptionRequestService;
        $this->exceptionErrorService = $exceptionErrorService;
        $this->exceptionHeaderWriter = $exceptionHeaderWriter;
        $this->classNameResolver = $classNameResolver;

        return $this;
    }

    public function __invoke(
        string $exceptionClass,
        object $controller,
        string $message,
        int $code = E_USER_ERROR,
        ?\Throwable $previous = null
    ): \Throwable {
        if ($exceptionClass !== '\fan\project\exception\plain\fatal') {
            throw new \InvalidArgumentException('Unsupported plain exception class "' . $exceptionClass . '".');
        }

        return new fatal(
            $controller,
            $message,
            $code,
            $previous,
            $this->exceptionDatabaseConnections,
            $this->exceptionRuntimeLogger,
            $this->exceptionRequestService,
            $this->exceptionErrorService,
            $this->exceptionHeaderWriter,
            $this->classNameResolver ?? static fn(object $object): string => \get_class_alt($object) ?? get_class($object)
        );
    }
}
