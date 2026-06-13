<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_core_project_error_storage_dependencies
{
    private application_core_project_error_log_writer_storage_dependencies $errorLogWriter;
    private application_core_project_error_file_storage_dependencies $errorFileStorage;

    public function __construct(container_interface $container)
    {
        $this->errorLogWriter = new application_core_project_error_log_writer_storage_dependencies($container);
        $this->errorFileStorage = new application_core_project_error_file_storage_dependencies($container);
    }

    public function errorLogWriter(): object
    {
        return $this->errorLogWriter->errorLogWriter();
    }

    public function errorFileStorage(): object
    {
        return $this->errorFileStorage->errorFileStorage();
    }
}
