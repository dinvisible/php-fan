<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_utility_service_registrar_dependencies
{
    private application_utility_date_state_registrar_dependencies $date;
    private application_utility_obfuscator_state_registrar_dependencies $obfuscator;
    private application_utility_image_modify_state_registrar_dependencies $imageModify;

    public function __construct(container_interface $container)
    {
        $this->date = new application_utility_date_state_registrar_dependencies($container);
        $this->obfuscator = new application_utility_obfuscator_state_registrar_dependencies($container);
        $this->imageModify = new application_utility_image_modify_state_registrar_dependencies($container);
    }

    public function dateState(): object
    {
        return $this->date->dateState();
    }

    public function obfuscatorState(): object
    {
        return $this->obfuscator->obfuscatorState();
    }

    public function imageModifyState(): object
    {
        return $this->imageModify->imageModifyState();
    }
}
