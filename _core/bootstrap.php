<?php

declare(strict_types=1);

/**
 * Main Load-runner of PHP-FAN files
 *
 * This file is part PHP-FAN (php-framework from Alexandr Nosov)
 * Copyright (C) 2005-2007 Alexandr Nosov, http://www.alex.4n.com.ua/
 *
 * Licensed under the terms of the GNU Lesser General Public License:
 *     http://www.opensource.org/licenses/lgpl-license.php
 *
 * Do not remove this comment if you want to use script!
 * Не удаляйте данный комментарий, если вы хотите использовать скрипт!
 *
 * @author: Alexandr Nosov (alex@4n.com.ua)
 * @version of file: 05.02.007 (31.08.2015)
 */
class bootstrap
{
    public const MIN_PHP_VERSION = '8.0.0';

    public const PHP_FAN_VERSION = '5.1.001';

    /**
     * Flag - is Load-runner already init
     * @var boolean
     */
    private static bool $isInit = false;

    /**
     * Flag - is CLI
     * @var boolean
     */
    private static bool $isCli = false;

    /**
     * Bootstrap configuration
     * @var array
     */
    private static array $config = [];

    /**
     * Replacement path elements
     * @var array
     */
    private static array $replacement = [];

    /**
     * Path to Error-log file
     * @var string
     */
    private static string $logDir = '{CORE_DIR}/../logs/bootstrap_log/';

    /**
     * Configurator of PHP parameters
     * @var object
     */
    private static ?object $initializer = null;

    /**
     * File Loader - also set autoload filles
     * @var object
     */
    private static ?object $loader = null;

    /**
     * Runner processing request
     * @var object
     */
    private static ?object $runner = null;

    /**
     * @var number Process ID
     */
    private static ?string $pid = null;

    public static function init(?string $iniPath = null): bool
    {
        if (version_compare(PHP_VERSION, self::MIN_PHP_VERSION) < 0) {
            die('PHP-FAN can\'t work with version less than "' . self::MIN_PHP_VERSION . '". Actually your version is "' . PHP_VERSION . '".');
        }
        if (self::$isInit) {
            return false;
        }
        self::$isInit = true;
        self::$isCli  = self::$isCli || strtolower(php_sapi_name()) === 'cli';

        // Define base const
        define('CORE_DIR', __DIR__);
        if (!defined('PROJECT_DIR')) {
            define('PROJECT_DIR', realpath(CORE_DIR . '/../_project'));
        }
        if (!defined('BASE_DIR')) {
            $docRoot = getenv('DOCUMENT_ROOT');
            if (empty($docRoot)) {
                $docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : dirname($_SERVER['SCRIPT_FILENAME']);
            }
            define('BASE_DIR', (string)$docRoot);
        }
        self::$replacement =[
            '{BASE_DIR}'    => BASE_DIR,
            '{CORE_DIR}'    => CORE_DIR,
            '{PROJECT_DIR}' => PROJECT_DIR,
        ];

        $composerAutoload = dirname(CORE_DIR) . '/vendor/autoload.php';
        if (is_readable($composerAutoload)) {
            require_once $composerAutoload;
        }

        // Include additional functions
        require_once CORE_DIR . '/functions.php';
        if (is_readable(PROJECT_DIR . '/functions.php')) {
            require_once PROJECT_DIR . '/functions.php';
        }

        // Load and preparse bootstrap configuration
        self::_setConfig($iniPath);

        if (!defined('ADMIN_EMAIL')) {
            define('ADMIN_EMAIL', self::$config['bootstrap']['admin_email']);
        }

        // Set initial error handler
        self::_setErrorHandler();

        // Perform the preparation procedures
        self::$initializer = self::_defineObj('initializer', '\fan\core\bootstrap\initializer', '{CORE_DIR}/bootstrap/initializer.php');
        self::$loader      = self::_defineObj('loader',      '\fan\core\bootstrap\loader',      '{CORE_DIR}/bootstrap/loader.php');
        self::$initializer->initAfterLoader();
        self::$runner      = self::_defineObj('runner',      '\fan\core\bootstrap\runner',      '{CORE_DIR}/bootstrap/runner.php');

        return true;
    }

    public static function run(?string $iniPath = null, bool $isEcho = true): mixed
    {
        self::init($iniPath);
        return self::getRunner()->run($isEcho);
    }

    public static function runCli(string $className, string $methodName = 'init'): mixed
    {
        if (php_sapi_name() !== 'cli') {
            die('This script can be run in CLI mode only');
        }
        self::$isCli = true;
        return self::getRunner()->runCli($className, $methodName);
    }

    public static function getInitializer(): \fan\core\bootstrap\initializer
    {
        if (empty(self::$initializer)) {
            self::init();
        }
        return self::$initializer;
    }

    public static function getLoader(): \fan\core\bootstrap\loader
    {
        if (empty(self::$loader)) {
            self::init();
        }
        return self::$loader;
    }

    public static function getRunner(): \fan\core\bootstrap\runner
    {
        if (empty(self::$runner)) {
            self::init();
        }
        return self::$runner;
    }

    public static function getConfigCache(): array
    {
        return isset(self::$config['config_cache']) ? self::$config['config_cache'] : [];
    }

    public static function loadClass(string $class, bool $makeAlias = true): mixed
    {
        return self::getLoader()->loadClass($class, $makeAlias);
    }

    /**
     * @param string $file File path or file descriptor handled by the operation.
     */
    public static function loadFile(string $file, int $handleError = 0, int $way = 0): mixed
    {
        return self::getLoader()->loadFile($file, $handleError, $way);
    }

    /**
     * Transforms path between supported representations.
     */
    public static function parsePath(string $path): string
    {
        return self::getLoader()->parsePath($path);
    }

    public static function getGlobalPath($key, $altPath = null): ?string
    {
        $paths = self::$config['bootstrap']['global_path'];
        $path  = empty($paths[$key]) ? $altPath : $paths[$key];
        return empty($path) ? null : self::_fillPlaceholder($path);
    }

    public static function handleError(int|float $errNo, string $errMsg, ?string $fileName = null, int|float|null $lineNum = null, $errContext = null): ?bool
    {
        if ($errNo === E_DEPRECATED || $errNo === E_USER_DEPRECATED) {
            return true;
        }
        self::logError('Error No ' . $errNo . ': ' . $errMsg . ' in ' . $fileName . ' on line ' . $lineNum . '. Context: ' . var_export($errContext, true));
        return null;
    }

    public static function logError(string $message): void
    {
        if (!empty($message)) {
            $logPath = self::$logDir;
            if (is_dir($logPath) && is_writable($logPath)) {
                $logPath .= '/' . date('Y-m-d') . '_000.log';
                if (!file_exists($logPath) || is_writable($logPath)) {
                    $row = date('H:i:s') . "\t" . addcslashes($message, "\\\t\r\n\0") . "\n";
                    error_log($row, 3, $logPath);
                    return;
                }
            }
            error_log($message, 0);
        }
    }

    public static function getPid(): string
    {
        if (!self::$pid) {
            self::$pid = uniqid();
        }
        return self::$pid;
    }

    public static function isCli(): bool
    {
        return self::$isCli;
    }

    protected static function _setConfig(mixed $iniPath): void
    {
        if (is_null($iniPath)) {
            $iniPath = PROJECT_DIR . '/conf/bootstrap.ini';
        }
        $config = file_exists((string)$iniPath) ? parse_ini_file((string)$iniPath, true) : [];
        self::$config = is_array($config) ? $config : [];
        foreach (self::$config as &$v1) {
            foreach ($v1 as $k => $v2) {
                if (strpos($k, '.')) {
                    unset($v1[$k]);
                    list($k1, $k2) = explode('.', $k, 2);
                    $v1[$k1][$k2] = $v2;
                }
            }
        }
    }

    protected static function _setErrorHandler(): void
    {
        self::$logDir = self::getGlobalPath('bootstrap_log', self::$logDir);

        if (!ini_get('date.timezone')) {
            ini_set('date.timezone', 'Europe/Helsinki');
        }
        set_error_handler([__CLASS__, 'handleError']);
    }


    protected static function _fillPlaceholder(string $path): string
    {
        foreach (self::$replacement as $k => $v) {
            $path = str_replace((string)$k, (string)$v, $path);
        }
        return $path;
    }

    protected static function _defineObj(string $key, string $class, string $path): object
    {
        if (isset(self::$config[$key])) {
            $conf  = self::$config[$key];
            $class = empty($conf['class']) ? $class : (string)$conf['class'];
            $path  = empty($conf['path'])  ? $path  : (string)$conf['path'];
        }
        require_once self::_fillPlaceholder($path);
        return new $class(isset($conf['ini']) ? $conf['ini'] : null);
    }

}
