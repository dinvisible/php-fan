<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\project\exception\fatal;


final class fatal_exception_factory
{
    public function __invoke(
        string $message,
        string $showMessage = '',
        string $errorFile = '',
        int $code = E_USER_ERROR,
        ?\Throwable $previous = null,
        ?object $requestInput = null,
        ?object $exceptionDatabaseConnections = null,
        ?object $exceptionRuntimeLogger = null,
        ?object $exceptionRequestService = null,
        ?object $exceptionErrorService = null,
        ?callable $requestInputFactory = null,
        ?object $exceptionHeaderWriter = null
    ): \Throwable {
        return new fatal(
            $message,
            $showMessage,
            $errorFile,
            $code,
            $previous,
            $requestInput,
            $exceptionDatabaseConnections,
            $exceptionRuntimeLogger,
            $exceptionRequestService,
            $exceptionErrorService,
            $requestInputFactory,
            $exceptionHeaderWriter
        );
    }
}
