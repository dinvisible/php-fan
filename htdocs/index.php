<?php

declare(strict_types=1);

use fan\core\di\web_application_initializer_defaults_factory;

require_once __DIR__ . '/../vendor/autoload.php';

((new web_application_initializer_defaults_factory())())->run();
