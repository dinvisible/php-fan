<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_utility_file_storage_dependencies
{
    private application_utility_php_array_file_loader_file_storage_dependencies $phpArrayFileLoader;
    private application_utility_soap_wsdl_file_storage_file_storage_dependencies $soapWsdlFileStorage;

    public function __construct(container_interface $container)
    {
        $this->phpArrayFileLoader = new application_utility_php_array_file_loader_file_storage_dependencies($container);
        $this->soapWsdlFileStorage = new application_utility_soap_wsdl_file_storage_file_storage_dependencies($container);
    }

    public function phpArrayFileLoader(): mixed
    {
        return $this->phpArrayFileLoader->phpArrayFileLoader();
    }

    public function soapWsdlFileStorage(): object
    {
        return $this->soapWsdlFileStorage->soapWsdlFileStorage();
    }
}
