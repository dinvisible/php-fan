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
            ->factory('config_state', static fn(container_interface $container): object => new config_state())
            ->factory('cache_state', static fn(container_interface $container): object => new cache_state())
            ->factory('cache_memcache_state', static fn(container_interface $container): object => new cache_memcache_state())
            ->factory('cookie_state', static fn(container_interface $container): object => new cookie_state())
            ->factory('pager_state', static fn(container_interface $container): object => new pager_state())
            ->factory('session_state', static fn(container_interface $container): object => new session_state($container->get('array_value_reader')))
            ->factory('tab_state', static fn(container_interface $container): object => new tab_state())
            ->factory('json_state', static fn(container_interface $container): object => new json_state())
            ->factory('service_listener_state', static fn(container_interface $container): object => new service_listener_state())
            ->factory('service_single_state', static fn(container_interface $container): object => new service_single_state())
            ->factory(
                'view_loader_state',
                static fn(container_interface $container): object => ($container->get('view_loader_state_factory'))()
            )
            ->factory('meta_maker_state', static fn(container_interface $container): object => new maker_state())
            ->factory('spec_file_image_row_state', static fn(container_interface $container): object => new row_state())
            ->factory('date_state', static fn(container_interface $container): object => new date_state())
            ->factory('curl_state', static fn(container_interface $container): object => new curl_state())
            ->factory('rest_state', static fn(container_interface $container): object => new rest_state())
            ->factory('image_modify_state', static fn(container_interface $container): object => new image_modify_state())
            ->factory('obfuscator_state', static fn(container_interface $container): object => new obfuscator_state())
            ->factory('file_system_state', static fn(container_interface $container): object => new file_system_state())
            ->factory('user_state', static fn(container_interface $container): object => new user_state());
    }
}
