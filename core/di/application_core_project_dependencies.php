<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_core_project_dependencies
{
    private application_core_project_error_dependencies $error;
    private application_core_project_storage_dependencies $storage;
    private application_core_project_tab_dependencies $tab;

    public function __construct(container_interface $container)
    {
        $this->error = new application_core_project_error_dependencies($container);
        $this->storage = new application_core_project_storage_dependencies($container);
        $this->tab = new application_core_project_tab_dependencies($container);
    }

    public function error(): object
    {
        return $this->error->error();
    }

    public function errorFactory(): callable
    {
        return $this->error->errorFactory();
    }

    public function reflectionClassFactory(): object
    {
        return $this->storage->reflectionClassFactory();
    }

    public function tab(): object
    {
        return $this->tab->tab();
    }

    public function tabFactory(): callable
    {
        return $this->tab->tabFactory();
    }

    public function metaFileStorage(): object
    {
        return $this->storage->metaFileStorage();
    }

    public function headerWriter(): object
    {
        return $this->storage->headerWriter();
    }

    public function phpArrayFileLoader(): object
    {
        return $this->storage->phpArrayFileLoader();
    }

    public function errorLogWriter(): object
    {
        return $this->error->errorLogWriter();
    }

    public function errorFileStorage(): object
    {
        return $this->error->errorFileStorage();
    }

    public function locale(): object
    {
        return $this->tab->locale();
    }

    public function application(): object
    {
        return $this->tab->application();
    }

    public function matcherRouteFileStorage(): object
    {
        return $this->tab->matcherRouteFileStorage();
    }
}
