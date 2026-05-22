<?php

declare(strict_types=1);

$phpFanRoot = dirname(__DIR__);

if (!defined('PHPUNIT_COMPOSER_INSTALL')) {
    require_once $phpFanRoot . '/_core/functions.php';

    $projectFunctions = $phpFanRoot . '/_project/functions.php';
    if (is_readable($projectFunctions)) {
        require_once $projectFunctions;
    }
}

spl_autoload_register(static function (string $class): void {
    $projectPrefix = 'fan\\project\\';
    if (!str_starts_with($class, $projectPrefix)) {
        return;
    }

    if (class_exists($class, false) || interface_exists($class, false) || trait_exists($class, false)) {
        return;
    }

    $coreClass = 'fan\\core\\' . substr($class, strlen($projectPrefix));
    if (!class_exists($coreClass) && !interface_exists($coreClass) && !trait_exists($coreClass)) {
        return;
    }

    class_alias($coreClass, $class, false);
});
