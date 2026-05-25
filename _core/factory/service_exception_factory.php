<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\base\service;
use fan\project\exception\service\fatal;


final class service_exception_factory
{
    public function __invoke(
        string $exceptionClass,
        service $service,
        string $message,
        int $code = E_USER_ERROR,
        ?\Throwable $previous = null
    ): \Throwable {
        if ($exceptionClass !== '\fan\project\exception\service\fatal') {
            throw new \InvalidArgumentException('Unsupported service exception class "' . $exceptionClass . '".');
        }

        return new fatal($service, $message, $code, $previous);
    }
}
