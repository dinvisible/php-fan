<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\base\meta\maker_state;
use fan\core\base\model\spec_file\image\row_state;
use fan\core\service\cache_memcache_state;
use fan\core\service\cache_state;
use fan\core\service\config_state;
use fan\core\service\cookie_state;
use fan\core\service\curl_state;
use fan\core\service\date_state;
use fan\core\service\file_system_state;
use fan\core\service\image_modify_state;
use fan\core\service\json_state;
use fan\core\service\obfuscator_state;
use fan\core\service\pager_state;
use fan\core\service\rest_state;
use fan\core\service\service_listener_state;
use fan\core\service\service_single_state;
use fan\core\service\session_state;
use fan\core\service\tab_state;
use fan\core\service\user_state;


final class application_state_registry
{
    public function register(container $container): container
    {
        return $container
            ->factory(service_id::CONFIG_STATE, static fn(container_interface $container): object => new config_state())
            ->factory(service_id::CACHE_STATE, static fn(container_interface $container): object => new cache_state())
            ->factory(service_id::CACHE_MEMCACHE_STATE, static fn(container_interface $container): object => new cache_memcache_state())
            ->factory(service_id::COOKIE_STATE, static fn(container_interface $container): object => new cookie_state())
            ->factory(service_id::PAGER_STATE, static fn(container_interface $container): object => new pager_state())
            ->factory(service_id::SESSION_STATE, static fn(container_interface $container): object => new session_state($container->get(service_id::ARRAY_VALUE_READER)))
            ->factory(service_id::TAB_STATE, static fn(container_interface $container): object => new tab_state())
            ->factory(service_id::JSON_STATE, static fn(container_interface $container): object => new json_state())
            ->factory(service_id::SERVICE_LISTENER_STATE, static fn(container_interface $container): object => new service_listener_state())
            ->factory(service_id::SERVICE_SINGLE_STATE, static fn(container_interface $container): object => new service_single_state())
            ->factory(
                service_id::VIEW_LOADER_STATE,
                static fn(container_interface $container): object => ($container->get(service_id::VIEW_LOADER_STATE_FACTORY))()
            )
            ->factory(service_id::META_MAKER_STATE, static fn(container_interface $container): object => new maker_state())
            ->factory(service_id::SPEC_FILE_IMAGE_ROW_STATE, static fn(container_interface $container): object => new row_state())
            ->factory(service_id::DATE_STATE, static fn(container_interface $container): object => new date_state())
            ->factory(service_id::CURL_STATE, static fn(container_interface $container): object => new curl_state())
            ->factory(service_id::REST_STATE, static fn(container_interface $container): object => new rest_state())
            ->factory(service_id::IMAGE_MODIFY_STATE, static fn(container_interface $container): object => new image_modify_state())
            ->factory(service_id::OBFUSCATOR_STATE, static fn(container_interface $container): object => new obfuscator_state())
            ->factory(service_id::FILE_SYSTEM_STATE, static fn(container_interface $container): object => new file_system_state())
            ->factory(service_id::USER_STATE, static fn(container_interface $container): object => new user_state());
    }
}
