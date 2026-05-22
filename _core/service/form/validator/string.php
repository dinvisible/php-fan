<?php

declare(strict_types=1);

namespace fan\core\service\form\validator;
/**
 * Compatibility autoload trigger for the string validator.
 *
 * PHP 7 reserves "string", so the concrete class is defined as
 * \fan\core\service\form\validator\string_validator.
 */
class_exists(string_validator::class);
