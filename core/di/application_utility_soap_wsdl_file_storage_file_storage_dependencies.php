<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_utility_soap_wsdl_file_storage_file_storage_dependencies
{
    public function __construct(private container_interface $container)
    {
    }

    public function soapWsdlFileStorage(): object
    {
        return $this->container->get(service_id::SOAP_WSDL_FILE_STORAGE);
    }
}
