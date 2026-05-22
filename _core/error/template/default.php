<?php

declare(strict_types=1);

return [
    0      => $this->setResponseHeader(200) . $this->setContentType('text'),
    'text' => $this->convArrayToSting($this->getTplVar()),
];
