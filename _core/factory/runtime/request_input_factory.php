<?php

declare(strict_types=1);

namespace fan\core\runtime;
use fan\core\service\request_input;


final class request_input_factory
{
    private \Closure $sourceFactory;

    public function __construct(callable $sourceFactory)
    {
        $this->sourceFactory = \Closure::fromCallable($sourceFactory);
    }

    public function __invoke(): object
    {
        $source = ($this->sourceFactory)();
        if (!is_object($source)) {
            throw new \RuntimeException('Request input source factory must return an object.');
        }

        return new request_input($source);
    }
}
