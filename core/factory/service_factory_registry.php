<?php

declare(strict_types=1);

namespace fan\core\di;

final class service_factory_registry
{
    /** @var array<string, \Closure> */
    private array $factories = [];

    /**
     * @param array<string, callable> $factories
     */
    public function __construct(array $factories)
    {
        foreach ($factories as $serviceName => $factory) {
            if (!is_string($serviceName) || $serviceName === '') {
                throw new \RuntimeException('Service factory registry keys must be non-empty strings.');
            }
            $this->factories[$serviceName] = \Closure::fromCallable($factory);
        }
    }

    public function get(string $serviceName): callable
    {
        if (!isset($this->factories[$serviceName])) {
            throw new \RuntimeException(sprintf('Service factory "%s" is not registered.', $serviceName));
        }

        return $this->factories[$serviceName];
    }
}
