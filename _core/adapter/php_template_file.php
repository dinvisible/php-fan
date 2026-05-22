<?php

declare(strict_types=1);

namespace fan\core\adapter;

class php_template_file
{
    /**
     * Renders a PHP template file with isolated local variables.
     */
    public static function render(string $path, array $variables = []): string
    {
        if (!is_readable($path)) {
            throw new \RuntimeException('PHP template file is not readable at "' . $path . '".');
        }

        ob_start();
        try {
            extract($variables, EXTR_OVERWRITE);
            include $path;
            return (string)ob_get_clean();
        } catch (\Throwable $exception) {
            ob_end_clean();
            throw $exception;
        }
    }
}
