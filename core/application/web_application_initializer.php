<?php

declare(strict_types=1);

namespace fan\core\bootstrap;

final class web_application_initializer
{
    public function __construct(
        private readonly request_runner $requestRunner,
        private readonly ?string $configPath,
        private readonly string $baseDir
    ) {
    }

    public function run(bool $isEcho = true): mixed
    {
        if (!defined('BASE_DIR')) {
            define('BASE_DIR', $this->baseDir);
        }

        return $this->requestRunner->run($this->configPath, $isEcho);
    }
}
