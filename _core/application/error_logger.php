<?php

declare(strict_types=1);

namespace fan\core\bootstrap;
use fan\core\runtime\error_logger;


/**
 * @deprecated Use \fan\core\runtime\error_logger instead.
 */
\class_alias(error_logger::class, __NAMESPACE__ . '\\error_logger');
