<?php

declare(strict_types=1);

namespace fan\core\adapter;

class php_template_file
{
    private \Closure $isReadable;
    private \Closure $templateExecutor;

    public function __construct(?callable $isReadable = null, ?callable $templateExecutor = null)
    {
        $this->isReadable = \Closure::fromCallable(
            $isReadable ?? static fn(string $path): bool => is_readable($path)
        );
        $this->templateExecutor = \Closure::fromCallable(
            $templateExecutor ?? static function (string $path, array $variables): string {
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
        );
    }

    public function __invoke(string $path, array $variables = []): string
    {
        return $this->renderFile($path, $variables);
    }

    /**
     * Renders a PHP template file with isolated local variables.
     */
    public static function render(string $path, array $variables = []): string
    {
        return (new self())->renderFile($path, $variables);
    }

    public function renderFile(string $path, array $variables = []): string
    {
        if (!($this->isReadable)($path)) {
            throw new \RuntimeException('PHP template file is not readable at "' . $path . '".');
        }

        return ($this->templateExecutor)($path, $variables);
    }
}
