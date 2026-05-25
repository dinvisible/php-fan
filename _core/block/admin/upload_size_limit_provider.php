<?php

declare(strict_types=1);

namespace fan\core\block\admin;

final class upload_size_limit_provider
{
    private object $phpRuntimeSettings;

    public function __construct(object $phpRuntimeSettings)
    {
        $this->phpRuntimeSettings = $phpRuntimeSettings;
    }

    public function __invoke(): string
    {
        return (string)$this->phpRuntimeSettings->get('upload_max_filesize');
    }
}
