<?php

declare(strict_types=1);

namespace fan\core\bootstrap;

final class bootstrap_object_factory
{
    private \Closure $configuredServiceFactory;

    public function __construct(callable $configuredServiceFactory)
    {
        $this->configuredServiceFactory = \Closure::fromCallable($configuredServiceFactory);
    }

    /**
     * @param list<mixed> $arguments
     */
    public function __invoke(string $class, array $arguments): object
    {
        $object = ($this->configuredServiceFactory)($class, $arguments);
        if (!is_object($object)) {
            throw new \RuntimeException('Bootstrap object factory must return an object.');
        }

        return $object;
    }
}
