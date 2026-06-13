<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_user_factory_dependencies
{
    private application_user_application_factory_dependencies $application;
    private application_user_identity_factory_dependencies $identity;
    private application_user_support_factory_dependencies $support;

    public function __construct(container_interface $container)
    {
        $this->application = new application_user_application_factory_dependencies($container);
        $this->identity = new application_user_identity_factory_dependencies($container);
        $this->support = new application_user_support_factory_dependencies($container);
    }

    public function configFactory(): callable
    {
        return $this->application->configFactory();
    }

    public function sessionFactory(): callable
    {
        return $this->identity->sessionFactory();
    }

    public function currentUserFactory(): callable
    {
        return $this->identity->currentUserFactory();
    }

    public function applicationFactory(): callable
    {
        return $this->application->applicationFactory();
    }

    public function errorFactory(): callable
    {
        return $this->support->errorFactory();
    }

    public function requestInputFactory(): callable
    {
        return $this->application->requestInputFactory();
    }

    public function entityFactory(): callable
    {
        return $this->support->entityFactory();
    }
}
