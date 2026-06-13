<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\block\base;


final class application_pager_service_creator
{
    private \Closure $projectServiceClassExists;

    public function __construct(?callable $projectServiceClassExists = null)
    {
        $this->projectServiceClassExists = \Closure::fromCallable(
            $projectServiceClassExists ?? static fn(string $className): bool => class_exists($className)
        );
    }

    public function createPagerService(
        container_interface $container,
        object $state,
        callable $pagerServiceFactory,
        string|base $block
    ): mixed {
        $pagerDependencies = $this->pagerDependencies($container);
        if (is_string($block)) {
            $block = $pagerDependencies->tab()->getTabBlock($block);
        }
        if (!is_object($block) || !($block instanceof base)) {
            throw $this->createError500Exception($container, 'Incorect call service pager. Please point block of data or its name.');
        }

        $name = $block->getBlockName();
        $instance = $state->getInstance($name);
        if ($instance === null) {
            $className = self::getProjectServiceClassName('pager');
            if (!$this->projectServiceClassExists($className)) {
                throw new \InvalidArgumentException('Service "pager" does not expose a project class.');
            }

            $instance = $pagerServiceFactory(
                $className,
                $block,
                $pagerDependencies->entityFactory(),
                $pagerDependencies->tabFactory(),
                $pagerDependencies->requestFactory(),
                $pagerDependencies->bootstrapRuntime(),
                $pagerDependencies->config(),
                $pagerDependencies->cacheFactory()
            );
            $state->setInstance($name, $instance);
        }

        return $instance;
    }

    private static function getProjectServiceClassName(string $serviceName): string
    {
        return '\fan\project\service\\' . trim($serviceName, " \t\n\r\0\x0B\\");
    }

    private function projectServiceClassExists(string $className): bool
    {
        return ($this->projectServiceClassExists)($className);
    }

    private function createError500Exception(
        container_interface $container,
        string $message,
        int $code = E_USER_ERROR,
        ?\Throwable $previous = null
    ): \Throwable {
        $factory = $this->pagerDependencies($container)->error500ExceptionFactory();
        if (!is_callable($factory)) {
            throw new \RuntimeException('Error500 exception factory must be callable.');
        }

        $exception = $factory($message, $code, $previous);
        if (!$exception instanceof \Throwable) {
            throw new \UnexpectedValueException('Error500 exception factory must return a throwable.');
        }

        return $exception;
    }

    private function pagerDependencies(container_interface $container): application_pager_service_dependencies
    {
        return new application_pager_service_dependencies($container);
    }
}
