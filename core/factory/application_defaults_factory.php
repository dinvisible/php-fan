<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\bootstrap\application;

final class application_defaults_factory
{
    private \Closure $applicationFactory;

    public function __construct(callable $applicationFactory)
    {
        $this->applicationFactory = \Closure::fromCallable($applicationFactory);
    }

    public function applicationFactory(): callable
    {
        return $this->applicationFactory;
    }

    public function errorHandlerFactory(): callable
    {
        return static function (application $application): callable {
            return static function (
                int|float $errNo,
                string $errMsg,
                ?string $fileName = null,
                int|float|null $lineNum = null,
                mixed $errContext = null
            ) use ($application): ?bool {
                if ($errNo === E_DEPRECATED || $errNo === E_USER_DEPRECATED) {
                    return true;
                }
                $application->logError('Error No ' . $errNo . ': ' . $errMsg . ' in ' . $fileName . ' on line ' . $lineNum . '. Context: ' . var_export($errContext, true));

                return null;
            };
        };
    }
}
