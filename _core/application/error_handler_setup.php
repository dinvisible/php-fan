<?php

declare(strict_types=1);

namespace fan\core\bootstrap;
use fan\core\runtime\error_handler_setup;


/**
 * @deprecated Use \fan\core\runtime\error_handler_setup instead.
 */
\class_alias(error_handler_setup::class, __NAMESPACE__ . '\\error_handler_setup');
