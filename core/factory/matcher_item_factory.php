<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\project\service\matcher\item;


final class matcher_item_factory
{
    private \Closure $fatalExceptionFactory;

    public function __construct(?callable $fatalExceptionFactory = null)
    {
        if ($fatalExceptionFactory !== null) {
            $this->fatalExceptionFactory = \Closure::fromCallable($fatalExceptionFactory);

            return;
        }

        $projectFatalFactory = new fatal_exception_factory();
        $this->fatalExceptionFactory = static fn(
            string $message,
            int $code = E_USER_ERROR,
            ?\Throwable $previous = null,
            ?object $requestInput = null
        ): \Throwable => $projectFatalFactory($message, '', '', $code, $previous, $requestInput);
    }

    public function __invoke(
        int $index,
        ?object $input,
        ?object $runtime,
        ?object $locale,
        ?object $application,
        ?object $routeFileStorage,
        ?callable $componentFactory,
        ?callable $serviceExceptionFactory = null
    ): object {
        return new item(
            $index,
            $input,
            $runtime,
            $locale,
            $application,
            $routeFileStorage,
            $componentFactory,
            $serviceExceptionFactory,
            $this->fatalExceptionFactory
        );
    }
}
