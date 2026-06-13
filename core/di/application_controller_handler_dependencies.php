<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_controller_handler_dependencies
{
    private application_controller_obfuscator_handler_dependencies $obfuscator;
    private application_controller_plain_file_handler_dependencies $plainFile;

    public function __construct(container_interface $container)
    {
        $this->obfuscator = new application_controller_obfuscator_handler_dependencies($container);
        $this->plainFile = new application_controller_plain_file_handler_dependencies($container);
    }

    public function obfuscatorFactory(): callable
    {
        return $this->obfuscator->obfuscatorFactory();
    }

    public function request(): object
    {
        return $this->obfuscator->request();
    }

    public function plainFileContext(): object
    {
        return $this->plainFile->plainFileContext();
    }
}
