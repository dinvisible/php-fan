#!/usr/bin/php
<?php


declare(strict_types=1);

$isError = false;
$rootDir = realpath(__DIR__ . '/..');

// ------- Make required directories ------- \\
makeReqDir([
    'logs'      => [
        'apache_log' => null,
        'bootstrap_log' => null,
        'data_log'      => null,
        'error_log'     => null,
        'message_log'   => null,
    ],
    'temp_data' => [
        'cache' => [
            'blocks'     => null,
            'common'     => null,
            'config'     => null,
            'entity'     => null,
            'file_store' => null,
            'img_nail'   => null,
            'template'   => null,
        ],
        'file_data' => [
            'file_info'  => null,
            'image_nail' => null,
        ],
        'nail'  => null,
        'other' => null,
    ],
], $rootDir);

// ------- Make Apache conf ------- \\
$domain = end($argv);
if (substr((string)$domain, -11) !== 'install.php') {
    makeApacheConf($rootDir, $domain);
} else {
    echo 'If you would like to make the config-file for Virtual host of the Apache, please run this file with domain: "install.php your.test.domain"' . "\n";
}

// ------- Finish ------- \\
if ($isError) {
    echo 'Process is finished with error.';
    sleep(7);
} else {
    echo 'Process is finished successfully.';
    sleep(2);
}

// ============== Local functions ============== \\
/**
 * Builds req dir for the framework helper.
 */
function makeReqDir(array $struct, $parentDir, int|float $level = 0): void
{
    foreach ($struct as $k => $v) {
        $dir = $parentDir . '/' . $k;

        if (!checkDir($dir, $level > 0 ? 0766 : 0666, $level > 0)) {
            continue;
        }

        if (!empty($v) && is_array($v)) {
            makeReqDir($v, $dir, $level + 1);
        }
    }
}

/**
 * Builds apache conf for the framework helper.
 */
function makeApacheConf($rootDir, string $domain): void
{
    global $isError;
    $confDir = $rootDir . '/httpd_conf';
    if (!checkDir($confDir, 0766)) {
        return;
    }

    $confFile = $confDir . '/' . str_replace('.', '_', $domain) . '.conf';
    do {
        for ($i = 0; $i < 20; $i++) {
            if (!is_file($confFile)) {
                break 2;
            }
            $confFile = substr($confFile, 0, -5) . '[' . $i . '].conf';
        }
        echo 'Error! To many file created for domain: ' . $domain;
        $isError = true;
    } while (false);

    // Make content of config
    if (substr($domain, 0, 4) === 'www.') {
        $domain = substr($domain, 4);
    }
    $rootDir = str_replace('\\', '/', $rootDir);
    $content = '<VirtualHost *:80>
    ServerAdmin admin@' . $domain . '
    DocumentRoot "' . $rootDir . '/htdocs"
    ServerAlias www.' . $domain . '
    ServerName ' . $domain . '

    ErrorLog ' . $rootDir . '/logs/apache_log/error.log
    CustomLog ' . $rootDir . '/logs/apache_log/access.log common

    <Directory ' . $rootDir . '/htdocs>
        Options Indexes FollowSymLinks
        AllowOverride All
        DirectoryIndex index.php index.html index.htm
        Order allow,deny
        Allow from all
    </Directory>
</VirtualHost>';

    file_put_contents($confFile, $content);
    echo 'Config file for Apache is saved to: ' . $confFile . "\n";
}

function checkDir(string $dir, int|float $mode, bool $wrRequired = true): bool
{
    global $isError;
    // ToDo: Checking of the privileges is incorrect for x-nix system. It is need to check web-server as owner directories
    if (is_file($dir)) {
       echo 'Error! Can\'t create directory. Such file exists there: ' . $dir . "\n";
       $isError = true;
       return false;
    } elseif (!is_dir($dir)) {
       echo 'Make directory: ' . $dir . "\n";
       if (!mkdir($dir, $mode)) {
           echo 'Error! Can\'t create directory: ' . $dir . "\n";
           $isError = true;
           return false;
       }
    } elseif ($wrRequired && !is_writable($dir)) {
       echo 'Error! Directory isn\'t writable: ' . $dir . "\n";
       $isError = true;
       return false;
    }
    return true;
}
