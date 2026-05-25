<?php

declare(strict_types=1);

namespace fan\core\bootstrap;

final class bootstrap_config_loader
{
    public function __invoke(context $context, ?string $configPath = null): void
    {
        $config = [];
        if ($configPath !== null && $context->bootstrapLoaderFileStorage()->exists((string)$configPath) && pathinfo((string)$configPath, PATHINFO_EXTENSION) === 'php') {
            $config = $context->loadPhpArrayFile((string)$configPath, []);
        }

        $context->state()->setConfig(array_replace_recursive($this->defaultConfig(), is_array($config) ? $config : []));
    }

    private function defaultConfig(): array
    {
        return [
            'bootstrap' => [
                'admin_email' => 'admin_email@domain.com',
                'global_path' => [
                    'apache_log' => '{CORE_DIR}/../logs/apache_log',
                    'bootstrap_log' => '{CORE_DIR}/../logs/bootstrap_log',
                    'config_source' => '{PROJECT_DIR}/conf',
                ],
            ],
            'initializer' => [
                'ini' => [
                    'main_1' => 'date.timezone: Europe/Kiev',
                    'main_2' => 'default_charset: UTF-8',
                    'check_adv_1' => 'mbstring.func_overload: 0',
                    'check_adv_2' => 'session.auto_start: 0',
                    'session_1' => 'session.use_trans_sid: 0',
                    'session_2' => 'session.use_only_cookies: 0',
                ],
            ],
            'loader' => [
                'cnt_alias_arg' => '3',
                'ini' => [
                    'dir_separator' => '/',
                    'app_dir' => '{PROJECT_DIR}/app/',
                    'model_dir' => '{PROJECT_DIR}/model/',
                    'capp_dir' => '{APP_DIR}/{APP_NAME}/',
                    'main_dir' => '{CAPP_DIR}/main/',
                    'temp_dir' => '{PROJECT_DIR}/../temp_data/',
                    'zend_dir' => '{PROJECT_DIR}/../libraries/Zend/',
                ],
            ],
            'runner' => [
                'config' => '{PROJECT_DIR}/conf/runner.php',
            ],
            'config_cache' => [
                'ENGINE' => 'file',
                'LIFETIME' => '0',
                'BASE_DIR' => '{PROJECT_DIR}/../temp_data/cache/config',
            ],
        ];
    }
}
