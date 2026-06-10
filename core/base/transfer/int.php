<?php

declare(strict_types=1);

namespace fan\core\base\transfer;
/**
 * Compatibility autoload trigger for the internal transfer class.
 *
 * PHP 7 reserves "int", so the concrete class is defined as
 * \fan\core\base\transfer\transfer_int.
 */
class_exists(transfer_int::class);
