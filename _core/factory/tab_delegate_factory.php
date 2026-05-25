<?php

declare(strict_types=1);

namespace fan\core\di;

final class tab_delegate_factory
{
    private \Closure $configuredServiceFactory;

    public function __construct(callable $configuredServiceFactory)
    {
        $this->configuredServiceFactory = \Closure::fromCallable($configuredServiceFactory);
    }

    public function __invoke(
        string $delegateClass,
        object $matcher,
        object $request,
        object $locale,
        ?callable $sessionFactory,
        object $input,
        callable $arrayValueReader
    ): object {
        return ($this->configuredServiceFactory)($delegateClass, [
            $matcher,
            $request,
            $locale,
            $sessionFactory,
            $input,
            $arrayValueReader
        ]);
    }

}
