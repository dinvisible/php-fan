<?php

declare(strict_types=1);

namespace fan\core\bootstrap;
use fan\core\runtime\error_handler_registrar;


/**
 * @deprecated Use \fan\core\runtime\error_handler_registrar instead.
 */
\class_alias(error_handler_registrar::class, __NAMESPACE__ . '\\error_handler_registrar');
