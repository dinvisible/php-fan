<?php

declare(strict_types=1);

namespace fan\core\runtime;
use fan\core\service\request_input_source;


final class request_input_source_factory
{
    private \Closure $globalsFactory;

    private \Closure $sourceProvider;

    public function __construct(callable $globalsFactory, ?callable $sourceProvider = null)
    {
        $this->globalsFactory = \Closure::fromCallable($globalsFactory);
        $this->sourceProvider = \Closure::fromCallable(
            $sourceProvider
                ?? static fn(object $globals): object => new request_input_source($globals)
        );
    }

    public function __invoke(): object
    {
        $globals = ($this->globalsFactory)();
        if (!is_object($globals)) {
            throw new \RuntimeException('Request input globals factory must return an object.');
        }

        $sourceProvider = $this->sourceProvider;

        return $sourceProvider($globals);
    }
}
