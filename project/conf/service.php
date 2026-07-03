<?php

declare(strict_types=1);

return array (
  'application' => 
  array (
    'PROJECT_NAME' => 'PHP-FAN',
    'DATE' => '2015-09-15',
    'VERSION' => '05.02.007',
    'used_names' => 
    array (
      0 => 'frontend',
      1 => 'admin',
    ),
  ),
  'cache' => 
  array (
    'DEFAULT_TYPE' => 'common_by_file',
    'DEFAULT_LIFETIME' => '7200',
    'TYPE' => 
    array (
      'common_by_file' => 
      array (
        'ENGINE' => 'file',
        'LIFETIME' => '86400',
        'BASE_DIR' => '{TEMP}/cache/common',
      ),
      'common_by_memcache' => 
      array (
        'ENGINE' => 'memcache',
        'HOST' => 'localhost',
        'PORT' => '11211',
        'LIFETIME' => '3600',
      ),
      'counter' => 
      array (
        'ENGINE' => 'memcache',
        'HOST' => 'localhost',
        'PORT' => '11211',
        'LIFETIME' => '86400',
      ),
      'service_data' => 
      array (
        'ENGINE' => 'file',
        'LIFETIME' => '1728000',
        'BASE_DIR' => '{TEMP}/cache/service_data',
      ),
      'file_store' => 
      array (
        'ENGINE' => 'file',
        'LIFETIME' => '1728000',
        'BASE_DIR' => '{TEMP}/cache/file_store',
      ),
      'img_nail' => 
      array (
        'ENGINE' => 'file',
        'LIFETIME' => '1728000',
        'BASE_DIR' => '{TEMP}/cache/img_nail',
      ),
    ),
  ),
  'config' => 
  array (
    'file' => 
    array (
      'entity' => 'entity',
    ),
    'app_file' => 
    array (
      'service' => 'service.{APP_NAME}',
      'entity' => 'entity.{APP_NAME}',
    ),
  ),
  'cookie' => 
  array (
    'DEFAULT_PATH' => '/',
    'DEFAULT_SECURE' => getenv('PHP_FAN_COOKIE_SECURE') === '0' ? '0' : '1',
    'DEFAULT_HTTP_ONLY' => '1',
    'DEFAULT_SAME_SITE' => 'Lax',
  ),
  'database' => 
  array (
    'DEFAULT_ENGINE' => 'mysql',
    'DEFAULT_CONNECTION' => 'common',
    'DEFAULT_SCENARIO' => 'read_uncommitted',
    'SQL_LNG_CORRECTION' => '1',
    'LOG_MORE_THAN' => '1000',
    'MAIL_MORE_THAN' => '2000',
    'DATABASE' => 
    array (
      'common' => 
      array (
        'ENGINE' => 'mysql',
        'PERSISTENT' => '0',
        'HOST' => getenv('PHP_FAN_DB_HOST') ?: 'localhost',
        'DATABASE' => getenv('PHP_FAN_DB_NAME') ?: 'php_fan',
        'USER' => getenv('PHP_FAN_DB_USER') ?: '',
        'PASSWORD' => getenv('PHP_FAN_DB_PASSWORD') ?: '',
      ),
      'test' => 
      array (
        'ENGINE' => 'mysql',
        'HOST' => getenv('PHP_FAN_TEST_DB_HOST') ?: 'localhost',
        'DATABASE' => getenv('PHP_FAN_TEST_DB_NAME') ?: 'php_fan_test',
        'USER' => getenv('PHP_FAN_TEST_DB_USER') ?: '',
        'PASSWORD' => getenv('PHP_FAN_TEST_DB_PASSWORD') ?: '',
        'SCENARIO' => 'autocommit',
      ),
    ),
    'SCENARIO' => 
    array (
      'read_uncommitted' => 
      array (
        'ISOLATION_LEVEL' => '1',
        'SQL' => 
        array (
          0 => 'SET CHARACTER SET \'UTF8\'',
          1 => 'SET AUTOCOMMIT=0',
          2 => 'SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED',
        ),
      ),
      'autocommit' => 
      array (
        'ISOLATION_LEVEL' => '0',
        'SQL' => 
        array (
          0 => 'SET CHARACTER SET \'UTF8\'',
          1 => 'SET AUTOCOMMIT=1',
        ),
      ),
    ),
  ),
  'eloquent' =>
  array (
    'DEFAULT_CONNECTION' => 'common',
    'SET_AS_GLOBAL' => '1',
    'BOOT_ELOQUENT' => '1',
    'CONNECTIONS' =>
    array (
      'common' =>
      array (
        'driver' => 'mysql',
        'host' => getenv('PHP_FAN_DB_HOST') ?: 'localhost',
        'database' => getenv('PHP_FAN_DB_NAME') ?: 'php_fan',
        'username' => getenv('PHP_FAN_DB_USER') ?: '',
        'password' => getenv('PHP_FAN_DB_PASSWORD') ?: '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
      ),
      'test' =>
      array (
        'driver' => 'mysql',
        'host' => getenv('PHP_FAN_TEST_DB_HOST') ?: 'localhost',
        'database' => getenv('PHP_FAN_TEST_DB_NAME') ?: 'php_fan_test',
        'username' => getenv('PHP_FAN_TEST_DB_USER') ?: '',
        'password' => getenv('PHP_FAN_TEST_DB_PASSWORD') ?: '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
      ),
    ),
  ),
  'date' => 
  array (
    'DEFAULT_FORMAT' => 
    array (
      0 => 'euro',
      1 => 'mysql',
    ),
    'CURRENT_TIMEZONE' => getenv('PHP_FAN_TIMEZONE') ?: 'UTC',
    'THIS_CENTURY_TO' => '50',
    'FORMAT' => 
    array (
      'mysql' => 
      array (
        'short_pattern' => 'Y-m-d',
        'full_pattern' => 'Y-m-d H:i:s',
      ),
      'usa' => 
      array (
        'short_pattern' => 'm.d.Y',
        'full_pattern' => 'm.d.Y H:i:s',
      ),
      'euro' => 
      array (
        'short_pattern' => 'd.m.Y',
        'full_pattern' => 'd.m.Y H:i:s',
      ),
    ),
  ),
  'debug' => 
  array (
    'ENABLED' => getenv('PHP_FAN_DEBUG') === '1' ? '1' : '',
    'DEBUG_IP' => '/^(127\\.0\\.0\\.1|192\\.168\\.\\d{1,3}\\.\\d{1,3})$/',
    'BORDER_OUT' => '#FFFFFF',
    'BORDER_INT' => '#7F7971',
    'BORDER_MAIN' => '#6600FF',
    'BORDER_IN' => '#FFFFFF',
    'HEAD_TEXT' => '#D6D1CA',
  ),
  'error' => 
  array (
    'DUPLICATE_BY_EMAIL' => 
    array (
      0 => '/(?<!\\.int)$/',
    ),
    'MAIL_TO' => 'admin@test.int',
    'NAME_TO' => 'Site Administrator',
    'MAIL_FILE' => '{TEMP}/error_',
    'SENT_TIME_LIMIT' => '10',
    'SYS_MASK' => '30719',
    'SYS_ERR' => 
    array (
      'warn' => '3754',
      'error' => '257',
      'fatal' => '84',
    ),
    'IGNORE_PATH' => 
    array (
      0 => 
      array (
        'mask' => '1034',
      ),
    ),
  ),
  'header' => 
  array (
    'SECURITY_HEADERS' =>
    array (
      'Content-Security-Policy' => "default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline'; object-src 'none'; base-uri 'self'; frame-ancestors 'self'",
      'Permissions-Policy' => 'camera=(), geolocation=(), microphone=()',
      'Referrer-Policy' => 'strict-origin-when-cross-origin',
      'X-Content-Type-Options' => 'nosniff',
      'X-Frame-Options' => 'SAMEORIGIN',
    ),
  ),
  'json' => 
  array (
    'ALLOW_INTERNAL' => '1',
    'DEPTH' => '25',
    'DECODE_OPTIONS' => '0',
    'ENCODE_OPTIONS' => '256',
  ),
  'monolog' =>
  array (
    'ENABLED' => '1',
    'CHANNEL' => 'php-fan',
    'HANDLER' => 'stream',
    'LEVEL' => 'error',
    'PATH' => '{CORE_DIR}/../logs/error_log/monolog.log',
    'BUBBLE' => '1',
    'FILE_PERMISSION' => '0664',
    'FORMAT' => "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
  ),
  'locale' => 
  array (
    'ENABLED' => '1',
    'DEFAULT_LANGUAGE' => 'ru',
    'CHARACTER_SET' => 'utf-8',
    'LANGUAGE_KEY' => 'lng',
    'COOKIE_TIME' => '2592000',
    'USE_SESSION4LNG' => '1',
    'REQUEST_HAS_LNG' => '1',
    'AVAILABLE_LANGUAGE' => 
    array (
      'en' => 'English',
      'ru' => 'Русский',
    ),
    'SHORT_NAME' => 
    array (
      'en' => 'eng',
      'ru' => 'рус',
    ),
  ),
  'matcher' => 
  array (
    'allow_switch_host' => '',
    'default_app' => 'frontend',
    'default_class' => 'index',
    'default_regexp_trim_ext' => '/^(.+)\\.(?:php|html?)/',
    'main_block_dir' => 'main',
    'default_handler' => 
    array (
      'key' => 'tab',
      'service' => 'tab',
      'method' => 'handleContent',
      'param' => '',
    ),
    'plain' => 
    array (
      'obfuscator' => 
      array (
        'definer' => 'request',
        'regexp' => '/^\\/get_(css|js)\\/(\\w+)$/',
        'class' => '\\fan\\project\\plain\\obfuscator',
        'method' => 'get{\\1}',
      ),
      'file' => 
      array (
        'definer' => 'request',
        'regexp' => '/^\\/(file)(?:\\/(.+?))?(?:\\.php|\\/)?/',
        'class' => '\\fan\\project\\plain\\db_file',
        'method' => 'get{\\1}',
      ),
      'image' => 
      array (
        'definer' => 'request',
        'regexp' => '/^\\/(image|nail|adm_nail)(?:\\/(.+?))?(?:\\.php|\\/)?/',
        'class' => '\\fan\\project\\plain\\image',
        'method' => 'get{\\1}',
      ),
    ),
    'app' => 
    array (
      'admin' => 
      array (
        'way' => 'request',
        'regexp' => '/^(\\/admin)(?:\\/|$)/',
        'prefix' => '1',
      ),
      'frontend' => 
      array (
        'way' => 'request',
        'regexp' => '/^\\/(?:({LANGUAGE})\\/)?(.*)/',
        'language' => '1',
        'path' => '2',
      ),
    ),
  ),
  'obfuscator' => 
  array (
    'css' => 
    array (
      'ENABLED' => '',
      'ENGINE' => 'simple',
      'GLUE' => '1',
      'CHECK_OBSOLETE' => '1',
      'PATH_CONTENT' => '{TEMP}/obfuscator/css/content',
      'PATH_META' => '{TEMP}/obfuscator/css/meta',
      'option' => 
      array (
        'DROP_COMMENTS' => '1',
        'DROP_END_ROW' => '1',
        'SPACES_TO_ONE' => '1',
      ),
    ),
    'js' => 
    array (
      'ENABLED' => '',
      'ENGINE' => 'simple',
      'GLUE' => '1',
      'CHECK_OBSOLETE' => '1',
      'PATH_CONTENT' => '{TEMP}/obfuscator/js/content',
      'PATH_META' => '{TEMP}/obfuscator/js/meta',
      'option' => 
      array (
        'DROP_COMMENTS' => '1',
        'DROP_END_ROW' => '1',
        'SPACES_TO_ONE' => '1',
      ),
    ),
  ),
  'pager' => 
  array (
    'PAGE_REQUEST_KEY' => 'page',
    'PAGE_REQUEST_SRC' => 'AG',
    'DEFAULT_ITEM_PER_PAGE' => '10',
    'PAGING_BY' => 'get',
  ),
  'plain' => 
  array (
    'engine_map' => 
    array (
      'file' => 'file_entity',
      'image' => 'file_entity',
      'other' => 'file_entity',
    ),
    'engine' => 
    array (
      'nail' => 
      array (
        'nail_dir' => '{TEMP}/nail',
      ),
      'adm_nail' => 
      array (
        'nailWidth' => '60',
        'nailHeight' => '60',
        'stubNail' => '{PROJECT}/data/image/adm_nail_stub.gif',
      ),
    ),
  ),
  'request' => 
  array (
    'DEFAULT_ORDER' => 'PAG',
    'ADD_REQUEST_DELIMITER' => '-',
    'ALLOW_SET' => 
    array (
      'G' => '_GET',
      'P' => '_POST',
      'R' => '_REQUEST',
    ),
  ),
  'rest' => 
  array (
    'ENABLED' => '1',
    'DEFAULT_CONNECTION' => '',
    'CONNECTION' => 
    array (
      'test' => 
      array (
        'url' => 
        array (
          'proyocol' => 'https',
          'server' => 'test.com',
          'request' => 'test/rest',
        ),
      ),
    ),
  ),
  'role' => 
  array (
  ),
  'session' => 
  array (
    'ENABLED' => '1',
    'ENGINE' => 'inbuilt',
    'SESSION_NAME' => 'SID',
    'MAXLIFETIME' => '1800',
    'COOKIE_SECURE' => getenv('PHP_FAN_COOKIE_SECURE') === '0' ? '0' : '1',
    'COOKIE_HTTPONLY' => '1',
    'COOKIE_SAMESITE' => 'Lax',
    'CACHE_LIMITER' => 'none',
    'CHECK_SYSTEM' => 
    array (
      0 => 'REMOTE_ADDR',
      1 => 'HTTP_USER_AGENT',
      2 => 'HTTP_X_REAL_IP',
    ),
    'KILL_BY_TIMEOUT' => '',
  ),
  'soap' => 
  array (
    'CACHE_ENABLED' => '',
    'TRACE_ENABLED' => '1',
    'WSDL_DIR' => '{PROJECT}/data/wsdl/',
    'BLOCK_SSL_VERIFY' => '',
    'PARAM' => 
    array (
      'soap_version' => '2',
    ),
  ),
  'tab' => 
  array (
    'MAX_QTT_TRANSFER' => '10',
    'error_403' => '~/error403',
    'error_404' => '~/error404',
    'error_500' => '~/error500',
    'default_extension' => 'html',
    'ALIAS_FILE_PATH' => '{PROJECT}/data/url_alias.php',
    'CHECK_PERFORMANCE' => '0',
    'debug_key' => 'debug',
    'DEFAULT_META' => 
    array (
      'html' => 
      array (
        'main' => 
        array (
          'root' => 'common/root',
        ),
        'common' => 
        array (
          'useMultiLanguage' => '1',
          'cache' => 
          array (
            'mode' => '0',
            'expire' => '1800',
          ),
        ),
      ),
      'loader' => 
      array (
      ),
    ),
    'VIEW_DEFINER' => 
    array (
      'default_format' => 'html',
      'rule' => 
      array (
        'loader' => 
        array (
          0 => 'PG.dl_ctrl.b.1',
          1 => '(APG.format.s.1)',
        ),
        'json' => 
        array (
          0 => '(H.X-Requested-With.s.0)&&(APG.format.s.1||APG.format.s.2)',
          1 => '(APG.format.s.2||APG.format.s.3)',
        ),
        'xml' => 
        array (
          0 => '(APG.format.s.4||APG.format.s.5)',
        ),
      ),
      'value' => 
      array (
        0 => 'XMLHttpRequest',
        1 => 'loader',
        2 => 'json',
        3 => 'JSON',
        4 => 'xml',
        5 => 'XML',
      ),
    ),
  ),
  'template' => 
  array (
    'NameSpace' => 'tpl',
    'PARENT_CLASS' => '\\fan\\project\\service\\template\\type\\base',
    'CACHE_DIR' => '{TEMP}/cache/template/',
    'UNIQUE_KEY_LENGH' => '7',
    'USE_STRIP' => '1',
  ),
  'timer' => 
  array (
    'ENABLE_EXEC' => getenv('PHP_FAN_TIMER_EXEC') === '1' ? '1' : '',
    'ENTITY' => 'mysql\\common\\timer_program',
    'JOINTLY_LIMIT' => '10',
    'TIMER_DIR' => '{PROJECT}/timer/',
    'BASE_NS' => '\\fan\\project\\timer',
    'PHP_INTERPRETER' => getenv('PHP_FAN_PHP_BINARY') ?: PHP_BINARY,
    'EXECUTABLE_ALLOWLIST' => array_values(array_filter([
      getenv('PHP_FAN_PHP_BINARY') ?: PHP_BINARY,
      substr(php_uname(), 0, 7) === 'Windows' ? 'at' : null,
    ])),
    'CRON_FILE' => '{PROJECT}/timer/crontab.php',
    'BGR_FILE' => '{PROJECT}/timer/background.php',
    'IS_AT_COMMAND' => '1',
  ),
  'translation' => 
  array (
    'ENABLED' => '1',
    'ALLOW_QUICK_MSG' => '1',
    'MSG_KEY_ENGL_ONLY' => '1',
    'MESSAGES_PATH' => '{PROJECT}/data/messages/messages_{LNG}.php',
    'USE_TAGS_PATH' => '{PROJECT}/data/messages/use_tags.php',
    'TAGS_PATH' => '{PROJECT}/data/messages/tags.php',
    'REFERERS_PATH' => '{PROJECT}/data/messages/referers.php',
    'MSG_PREFIX' => 
    array (
      'TITLE' => 'HTML-title',
      'NAV' => 'Site navigation',
      'LABEL' => 'Form label',
      'ERROR' => 'Form error mesage',
      'NOTE' => 'Form note',
      'VALUE' => 'Form element value',
      'BUTTON' => 'Button text',
      'HEADER' => 'Text header',
      'LINK' => 'Text of link',
      'INFO' => 'Information element',
      'META' => 'Meta tag text',
      'OTHER' => 'Other element',
    ),
  ),
  'user' => 
  array (
    'DEFAULT_SPACE' => 'user_by_entity',
    'LOGOUT_FIELD' => 'logout',
    'LOGOUT_ORDER' => 'GP',
    'space' => 
    array (
      'user_by_entity' => 
      array (
        'APPLICATIONS' => 
        array (
          0 => 'frontend',
          1 => 'forum',
        ),
        'ENGINE' => 'entity',
        'ENGINE_KEY' => 'member',
        'IDENTIFYERS' => 
        array (
          0 => 'login',
          1 => 'email',
        ),
      ),
      'admin_by_entity' => 
      array (
        'APPLICATIONS' => 
        array (
          0 => 'admin',
        ),
        'ENGINE' => 'entity',
        'ENGINE_KEY' => 'administrator',
        'IDENTIFYERS' => 
        array (
          0 => 'login',
        ),
        'LOG_ERR_AUTH' => '1',
      ),
      'test_usr' => 
      array (
        'APPLICATIONS' => 
        array (
          0 => 'frontend',
        ),
        'ENGINE' => 'config',
        'ENGINE_SOURCE' => 'auth_test',
        'ENGINE_KEY' => 'test_user',
        'IDENTIFYERS' => 
        array (
          0 => 'login',
        ),
      ),
    ),
  ),
);
