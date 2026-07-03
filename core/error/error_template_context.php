<?php

declare(strict_types=1);

namespace fan\core\error;

interface error_template_context
{
    public function setResponseHeader(mixed $code): string;

    public function setContentType(mixed $type): string;

    public function setDoctype(): string;

    public function getTplVar(?string $key = null): mixed;

    public function convArrayToSting(mixed $src, string $glue = "\n"): string;
}
