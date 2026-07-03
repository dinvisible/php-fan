<?php

declare(strict_types=1);

use fan\core\error\error_template_context;

return static fn(error_template_context $demonstrator): array => [
    0      => $demonstrator->setResponseHeader(200) . $demonstrator->setContentType('text'),
    'text' => $demonstrator->convArrayToSting($demonstrator->getTplVar()),
];
