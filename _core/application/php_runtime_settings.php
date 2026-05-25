<?php

declare(strict_types=1);

namespace fan\core\bootstrap;
use fan\core\runtime\php_runtime_settings;


/**
 * @deprecated Use \fan\core\runtime\php_runtime_settings instead.
 */
\class_alias(php_runtime_settings::class, __NAMESPACE__ . '\\php_runtime_settings');
