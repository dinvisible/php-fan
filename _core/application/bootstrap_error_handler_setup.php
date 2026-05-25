<?php

declare(strict_types=1);

namespace fan\core\bootstrap;

final class bootstrap_error_handler_setup
{
    public function __invoke(context $context, callable $handler): void
    {
        $state = $context->state();
        $state->setLogDir($this->globalPath($state, 'bootstrap_log', $state->logDir()) ?? $state->logDir());

        $context->setupErrorHandler($handler);
    }

    private function globalPath(state $state, string $key, mixed $altPath = null): ?string
    {
        $config = $state->config();
        $paths = $config['bootstrap']['global_path'] ?? [];
        $path = empty($paths[$key]) ? $altPath : $paths[$key];

        return empty($path) ? null : $state->fillPlaceholder((string)$path);
    }
}
