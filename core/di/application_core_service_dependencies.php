<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_core_service_dependencies
{
    private application_core_request_dependencies $request;
    private application_core_user_session_dependencies $userSession;
    private application_core_project_dependencies $project;

    public function __construct(container_interface $container)
    {
        $this->request = new application_core_request_dependencies($container);
        $this->userSession = new application_core_user_session_dependencies($container);
        $this->project = new application_core_project_dependencies($container);
    }

    public function requestInput(): object
    {
        return $this->request->requestInput();
    }

    public function jsonFactory(): callable
    {
        return $this->request->jsonFactory();
    }

    public function cookieFactory(): callable
    {
        return $this->request->cookieFactory();
    }

    public function matcherFactory(): callable
    {
        return $this->request->matcherFactory();
    }

    public function requestFactory(): callable
    {
        return $this->request->requestFactory();
    }

    public function arrayAdducer(): callable
    {
        return $this->request->arrayAdducer();
    }

    public function recursiveMerger(): callable
    {
        return $this->request->recursiveMerger();
    }

    public function arrayValueReader(): callable
    {
        return $this->request->arrayValueReader();
    }

    public function classNameResolver(): callable
    {
        return $this->request->classNameResolver();
    }

    public function currentUserFactory(): callable
    {
        return $this->userSession->currentUserFactory();
    }

    public function sessionFactory(): callable
    {
        return $this->userSession->sessionFactory();
    }

    public function error(): object
    {
        return $this->project->error();
    }

    public function errorFactory(): callable
    {
        return $this->project->errorFactory();
    }

    public function currentUserSpaceFactory(): callable
    {
        return $this->userSession->currentUserSpaceFactory();
    }

    public function dateFactory(): callable
    {
        return $this->userSession->dateFactory();
    }

    public function reflectionClassFactory(): object
    {
        return $this->project->reflectionClassFactory();
    }

    public function tab(): object
    {
        return $this->project->tab();
    }

    public function tabFactory(): callable
    {
        return $this->project->tabFactory();
    }

    public function metaFileStorage(): object
    {
        return $this->project->metaFileStorage();
    }

    public function headerWriter(): object
    {
        return $this->project->headerWriter();
    }

    public function phpArrayFileLoader(): object
    {
        return $this->project->phpArrayFileLoader();
    }

    public function errorLogWriter(): object
    {
        return $this->project->errorLogWriter();
    }

    public function errorFileStorage(): object
    {
        return $this->project->errorFileStorage();
    }

    public function locale(): object
    {
        return $this->project->locale();
    }

    public function application(): object
    {
        return $this->project->application();
    }

    public function matcherRouteFileStorage(): object
    {
        return $this->project->matcherRouteFileStorage();
    }

    public function entityFactory(): callable
    {
        return $this->userSession->entityFactory();
    }
}
