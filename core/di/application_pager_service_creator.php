<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\block\base;


final class application_pager_service_creator
{
    public function createPagerService(
        container_interface $container,
        object $state,
        callable $pagerServiceFactory,
        string|base $block
    ): mixed {
        if (is_string($block)) {
            $block = $container->get(service_id::TAB)->getTabBlock($block);
        }
        if (!is_object($block) || !($block instanceof base)) {
            throw $this->createError500Exception($container, 'Incorect call service pager. Please point block of data or its name.');
        }

        $name = $block->getBlockName();
        $instance = $state->getInstance($name);
        if ($instance === null) {
            $className = self::getProjectServiceClassName('pager');
            if (!class_exists($className)) {
                throw new \InvalidArgumentException('Service "pager" does not expose a project class.');
            }

            $instance = $pagerServiceFactory(
                $className,
                $block,
                static fn(): mixed => $container->get(service_id::ENTITY),
                static fn(): mixed => $container->get(service_id::TAB),
                static fn(): mixed => $container->get(service_id::REQUEST),
                $container->get(service_id::BOOTSTRAP_RUNTIME),
                $container->get(service_id::CONFIG),
                static fn(string $type): mixed => $container->get(service_id::CACHE, $type)
            );
            $state->setInstance($name, $instance);
        }

        return $instance;
    }

    private static function getProjectServiceClassName(string $serviceName): string
    {
        return '\fan\project\service\\' . trim($serviceName, " \t\n\r\0\x0B\\");
    }

    private function createError500Exception(
        container_interface $container,
        string $message,
        int $code = E_USER_ERROR,
        ?\Throwable $previous = null
    ): \Throwable {
        $factory = $container->get(service_id::ERROR500_EXCEPTION_FACTORY);
        if (!is_callable($factory)) {
            throw new \RuntimeException('Error500 exception factory must be callable.');
        }

        $exception = $factory($message, $code, $previous);
        if (!$exception instanceof \Throwable) {
            throw new \UnexpectedValueException('Error500 exception factory must return a throwable.');
        }

        return $exception;
    }
}
