<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\base\model\entity;


final class model_entity_exception_factory
{
    private \Closure $configuredServiceFactory;

    public function __construct(callable $configuredServiceFactory)
    {
        $this->configuredServiceFactory = \Closure::fromCallable($configuredServiceFactory);
    }

    public function __invoke(
        string $exceptionClass,
        entity $entity,
        string $message,
        int $code = E_USER_ERROR,
        ?\Throwable $previous = null
    ): \Throwable {
        return ($this->configuredServiceFactory)($exceptionClass, [
            $entity,
            $message,
            $code,
            $previous
        ]);
    }
}

