<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_support_exception_factory_registrar_dependencies
{
    private application_support_bootstrap_runtime_registrar_dependencies $bootstrapRuntime;
    private application_support_request_registrar_dependencies $request;
    private application_support_error_registrar_dependencies $error;
    private application_support_header_writer_registrar_dependencies $headerWriter;

    public function __construct(container_interface $container)
    {
        $this->bootstrapRuntime = new application_support_bootstrap_runtime_registrar_dependencies($container);
        $this->request = new application_support_request_registrar_dependencies($container);
        $this->error = new application_support_error_registrar_dependencies($container);
        $this->headerWriter = new application_support_header_writer_registrar_dependencies($container);
    }

    public function bootstrapRuntime(): object
    {
        return $this->bootstrapRuntime->bootstrapRuntime();
    }

    public function request(): object
    {
        return $this->request->request();
    }

    public function error(): object
    {
        return $this->error->error();
    }

    public function headerWriter(): object
    {
        return $this->headerWriter->headerWriter();
    }
}
