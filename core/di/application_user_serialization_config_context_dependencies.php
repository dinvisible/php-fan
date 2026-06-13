<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_user_serialization_config_context_dependencies
{
    private application_user_serializer_operations_serialization_config_context_dependencies $serializerOperations;
    private application_user_config_serialization_config_context_dependencies $config;

    public function __construct(container_interface $container)
    {
        $this->serializerOperations = new application_user_serializer_operations_serialization_config_context_dependencies($container);
        $this->config = new application_user_config_serialization_config_context_dependencies($container);
    }

    public function serializerOperations(): mixed
    {
        return $this->serializerOperations->serializerOperations();
    }

    public function config(): mixed
    {
        return $this->config->config();
    }
}
