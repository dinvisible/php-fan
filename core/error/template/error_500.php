<?php

declare(strict_types=1);

use fan\core\error\error_template_context;

return static fn(error_template_context $demonstrator): array => [
    0         => $demonstrator->setResponseHeader(500) . $demonstrator->setContentType('xhtml'),
    'doctype' => $demonstrator->setDoctype(),
    'text'    => $demonstrator->convArrayToSting($demonstrator->getTplVar(), '</p><p>'),
];
