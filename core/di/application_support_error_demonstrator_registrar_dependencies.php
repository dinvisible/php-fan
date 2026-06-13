<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_support_error_demonstrator_registrar_dependencies
{
    private application_support_header_writer_registrar_dependencies $headerWriter;
    private application_support_error_log_writer_registrar_dependencies $errorLogWriter;
    private application_support_error_demonstrator_file_storage_registrar_dependencies $errorDemonstratorFileStorage;
    private application_support_error_demonstrator_loader_registrar_dependencies $errorDemonstratorLoader;

    public function __construct(container_interface $container)
    {
        $this->headerWriter = new application_support_header_writer_registrar_dependencies($container);
        $this->errorLogWriter = new application_support_error_log_writer_registrar_dependencies($container);
        $this->errorDemonstratorFileStorage = new application_support_error_demonstrator_file_storage_registrar_dependencies($container);
        $this->errorDemonstratorLoader = new application_support_error_demonstrator_loader_registrar_dependencies($container);
    }

    public function headerWriter(): object
    {
        return $this->headerWriter->headerWriter();
    }

    public function errorLogWriter(): object
    {
        return $this->errorLogWriter->errorLogWriter();
    }

    public function errorDemonstratorFileStorage(): object
    {
        return $this->errorDemonstratorFileStorage->errorDemonstratorFileStorage();
    }

    public function errorDemonstratorLoader(): object
    {
        return $this->errorDemonstratorLoader->errorDemonstratorLoader();
    }
}
