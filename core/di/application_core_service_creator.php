<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_core_service_creator
{
    private \Closure $projectServiceClassExists;

    public function __construct(?callable $projectServiceClassExists = null)
    {
        $this->projectServiceClassExists = \Closure::fromCallable(
            $projectServiceClassExists ?? static fn(string $className): bool => class_exists($className)
        );
    }

    public function createRequestService(container_interface $container, callable $requestServiceFactory): mixed
    {
        $className = self::getProjectServiceClassName('request');
        if (!$this->projectServiceClassExists($className)) {
            throw new \InvalidArgumentException('Service "request" does not expose a project class.');
        }
        $common = $this->commonDependencies($container);
        $coreDependencies = $this->coreDependencies($container);

        return $requestServiceFactory(
            $className,
            $coreDependencies->requestInput(),
            $common->bootstrapRuntime(),
            $coreDependencies->jsonFactory(),
            $coreDependencies->cookieFactory(),
            $coreDependencies->matcherFactory(),
            $common->bootstrapRuntime(),
            $common->config(),
            $common->cacheFactory(),
            $coreDependencies->arrayAdducer(),
            $coreDependencies->recursiveMerger(),
            $coreDependencies->arrayValueReader(),
            $coreDependencies->classNameResolver()
        );
    }

    public function createRoleService(container_interface $container, callable $roleServiceFactory): mixed
    {
        $className = self::getProjectServiceClassName('role');
        if (!$this->projectServiceClassExists($className)) {
            throw new \InvalidArgumentException('Service "role" does not expose a project class.');
        }
        $common = $this->commonDependencies($container);
        $coreDependencies = $this->coreDependencies($container);

        return $roleServiceFactory(
            $className,
            $coreDependencies->currentUserFactory(),
            $coreDependencies->sessionFactory(),
            $coreDependencies->error(),
            $coreDependencies->currentUserSpaceFactory(),
            $coreDependencies->dateFactory(),
            $common->bootstrapRuntime(),
            $common->config(),
            $common->cacheFactory()
        );
    }

    public function createReflectorService(container_interface $container, callable $reflectorServiceFactory): mixed
    {
        $className = self::getProjectServiceClassName('reflector');
        if (!$this->projectServiceClassExists($className)) {
            throw new \InvalidArgumentException('Service "reflector" does not expose a project class.');
        }
        $common = $this->commonDependencies($container);
        $coreDependencies = $this->coreDependencies($container);

        return $reflectorServiceFactory(
            $className,
            $common->bootstrapRuntime(),
            $common->config(),
            $common->cacheFactory(),
            $coreDependencies->reflectionClassFactory()
        );
    }

    public function createApplicationService(container_interface $container, callable $applicationServiceFactory): mixed
    {
        $className = self::getProjectServiceClassName('application');
        if (!$this->projectServiceClassExists($className)) {
            throw new \InvalidArgumentException('Service "application" does not expose a project class.');
        }
        $common = $this->commonDependencies($container);
        $coreDependencies = $this->coreDependencies($container);

        return $applicationServiceFactory(
            $className,
            true,
            $common->bootstrapRuntime(),
            $common->bootstrapRuntime(),
            $common->config(),
            $common->cacheFactory(),
            $coreDependencies->arrayAdducer()
        );
    }

    public function createDebugService(container_interface $container, callable $debugServiceFactory): mixed
    {
        $className = self::getProjectServiceClassName('debug');
        if (!$this->projectServiceClassExists($className)) {
            throw new \InvalidArgumentException('Service "debug" does not expose a project class.');
        }
        $common = $this->commonDependencies($container);
        $coreDependencies = $this->coreDependencies($container);

        return $debugServiceFactory(
            $className,
            true,
            $coreDependencies->tab(),
            $coreDependencies->requestInput(),
            $common->bootstrapRuntime(),
            $common->config(),
            $common->cacheFactory(),
            $coreDependencies->metaFileStorage(),
            $coreDependencies->arrayAdducer(),
            $coreDependencies->reflectionClassFactory()
        );
    }

    public function createHeaderService(container_interface $container, callable $headerServiceFactory): mixed
    {
        $className = self::getProjectServiceClassName('header');
        if (!$this->projectServiceClassExists($className)) {
            throw new \InvalidArgumentException('Service "header" does not expose a project class.');
        }
        $common = $this->commonDependencies($container);
        $coreDependencies = $this->coreDependencies($container);

        return $headerServiceFactory(
            $className,
            true,
            $coreDependencies->requestInput(),
            $coreDependencies->headerWriter(),
            $common->bootstrapRuntime(),
            $common->config(),
            $common->cacheFactory(),
            $coreDependencies->recursiveMerger()
        );
    }

    public function createErrorService(container_interface $container, callable $errorServiceFactory): mixed
    {
        $className = self::getProjectServiceClassName('error');
        if (!$this->projectServiceClassExists($className)) {
            throw new \InvalidArgumentException('Service "error" does not expose a project class.');
        }
        $common = $this->commonDependencies($container);
        $coreDependencies = $this->coreDependencies($container);

        return $errorServiceFactory(
            $className,
            true,
            $coreDependencies->requestInput(),
            $common->bootstrapRuntime(),
            null,
            null,
            $common->bootstrapRuntime(),
            $common->config(),
            $common->cacheFactory(),
            $coreDependencies->phpArrayFileLoader(),
            $coreDependencies->errorLogWriter(),
            $coreDependencies->errorFileStorage()
        );
    }

    public function createMatcherService(
        container_interface $container,
        callable $matcherItemFactory,
        callable $matcherItemComponentFactory,
        callable $matcherServiceFactory
    ): mixed
    {
        $className = self::getProjectServiceClassName('matcher');
        if (!$this->projectServiceClassExists($className)) {
            throw new \InvalidArgumentException('Service "matcher" does not expose a project class.');
        }
        $common = $this->commonDependencies($container);
        $coreDependencies = $this->coreDependencies($container);

        return $matcherServiceFactory(
            $className,
            true,
            $coreDependencies->requestInput(),
            $common->bootstrapRuntime(),
            $coreDependencies->locale(),
            $coreDependencies->application(),
            $coreDependencies->matcherRouteFileStorage(),
            $matcherItemFactory,
            $matcherItemComponentFactory,
            $common->bootstrapRuntime(),
            $common->config(),
            $common->cacheFactory()
        );
    }

    public function createTimerService(
        container_interface $container,
        callable $timerProgramFactory,
        callable $timerServiceFactory
    ): mixed
    {
        $className = self::getProjectServiceClassName('timer');
        if (!$this->projectServiceClassExists($className)) {
            throw new \InvalidArgumentException('Service "timer" does not expose a project class.');
        }
        $common = $this->commonDependencies($container);
        $coreDependencies = $this->coreDependencies($container);

        return $timerServiceFactory(
            $className,
            $common->bootstrapRuntime(),
            $coreDependencies->dateFactory(),
            $coreDependencies->entityFactory(),
            $coreDependencies->errorFactory(),
            null,
            null,
            $timerProgramFactory,
            $common->bootstrapRuntime(),
            $common->config(),
            $common->cacheFactory()
        );
    }

    public function createLocaleService(container_interface $container, callable $localeServiceFactory): mixed
    {
        $className = self::getProjectServiceClassName('locale');
        if (!$this->projectServiceClassExists($className)) {
            throw new \InvalidArgumentException('Service "locale" does not expose a project class.');
        }
        $common = $this->commonDependencies($container);
        $coreDependencies = $this->coreDependencies($container);

        return $localeServiceFactory(
            $className,
            true,
            $coreDependencies->entityFactory(),
            $coreDependencies->tabFactory(),
            $coreDependencies->sessionFactory(),
            $coreDependencies->requestFactory(),
            $coreDependencies->cookieFactory(),
            $coreDependencies->matcherFactory(),
            $common->bootstrapRuntime(),
            $common->config(),
            $common->cacheFactory(),
            $coreDependencies->arrayAdducer(),
            $coreDependencies->classNameResolver()
        );
    }

    private function commonDependencies(container_interface $container): application_creator_common_dependencies
    {
        return new application_creator_common_dependencies($container);
    }

    private function coreDependencies(container_interface $container): application_core_service_dependencies
    {
        return new application_core_service_dependencies($container);
    }

    private static function getProjectServiceClassName(string $serviceName): string
    {
        return '\fan\project\service\\' . trim($serviceName, " \t\n\r\0\x0B\\");
    }

    private function projectServiceClassExists(string $className): bool
    {
        return ($this->projectServiceClassExists)($className);
    }
}
