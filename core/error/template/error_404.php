<?php

declare(strict_types=1);

use fan\core\error\error_template_context;

return static fn(error_template_context $demonstrator): array => [
    0      => $demonstrator->setResponseHeader(404) . $demonstrator->setContentType('html'),
    'doctype' => $demonstrator->setDoctype(),
    //'text'    => $this->convArrayToSting($this->getTplVar(), '</p><p>'),
];
