<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_controller_obfuscator_handler_dependencies
{
    private application_controller_obfuscator_factory_handler_dependencies $obfuscatorFactory;
    private application_controller_request_handler_dependencies $request;

    public function __construct(container_interface $container)
    {
        $this->obfuscatorFactory = new application_controller_obfuscator_factory_handler_dependencies($container);
        $this->request = new application_controller_request_handler_dependencies($container);
    }

    public function obfuscatorFactory(): callable
    {
        return $this->obfuscatorFactory->obfuscatorFactory();
    }

    public function request(): object
    {
        return $this->request->request();
    }
}
