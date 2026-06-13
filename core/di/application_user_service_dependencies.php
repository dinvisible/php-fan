<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_user_service_dependencies
{
    private application_user_context_dependencies $context;
    private application_user_factory_dependencies $factory;
    private application_user_runtime_dependencies $runtime;

    public function __construct(container_interface $container)
    {
        $this->context = new application_user_context_dependencies($container);
        $this->factory = new application_user_factory_dependencies($container);
        $this->runtime = new application_user_runtime_dependencies($container);
    }

    public function serializerOperations(): mixed
    {
        return $this->context->serializerOperations();
    }

    public function config(): mixed
    {
        return $this->context->config();
    }

    public function request(): mixed
    {
        return $this->context->request();
    }

    public function application(): mixed
    {
        return $this->context->application();
    }

    public function error500ExceptionFactory(): mixed
    {
        return $this->context->error500ExceptionFactory();
    }

    public function session(string $namespace, string $group): mixed
    {
        return $this->context->session($namespace, $group);
    }

    public function configFactory(): callable
    {
        return $this->factory->configFactory();
    }

    public function sessionFactory(): callable
    {
        return $this->factory->sessionFactory();
    }

    public function currentUserFactory(): callable
    {
        return $this->factory->currentUserFactory();
    }

    public function applicationFactory(): callable
    {
        return $this->factory->applicationFactory();
    }

    public function errorFactory(): callable
    {
        return $this->factory->errorFactory();
    }

    public function requestInputFactory(): callable
    {
        return $this->factory->requestInputFactory();
    }

    public function entityFactory(): callable
    {
        return $this->factory->entityFactory();
    }

    public function bootstrapRuntime(): mixed
    {
        return $this->runtime->bootstrapRuntime();
    }

    public function cacheFactory(): callable
    {
        return $this->runtime->cacheFactory();
    }

    public function arrayAdducer(): mixed
    {
        return $this->runtime->arrayAdducer();
    }
}
