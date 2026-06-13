<?php

declare(strict_types=1);

namespace fan\core\adapter;

use fan\core\block\base;
use Twig\Environment;
use Twig\TwigFunction;
use Twig\Loader\ArrayLoader;

final class twig_template_service
{
    private \Closure $twigClassExists;
    private \Closure $isReadable;
    private \Closure $fileReader;

    public function __construct(
        ?callable $twigClassExists = null,
        ?callable $isReadable = null,
        ?callable $fileReader = null
    )
    {
        $this->twigClassExists = \Closure::fromCallable(
            $twigClassExists ?? static fn(string $className): bool => class_exists($className)
        );
        $this->isReadable = \Closure::fromCallable(
            $isReadable ?? static fn(string $path): bool => is_readable($path)
        );
        $this->fileReader = \Closure::fromCallable(
            $fileReader ?? static fn(string $path): string|false => file_get_contents($path)
        );
    }

    public function get(string $templatePath, mixed $parent = null, ?base $block = null): object
    {
        return new twig_template_file($templatePath, $block, $this->twigClassExists, $this->isReadable, $this->fileReader);
    }
}

final class twig_template_file
{
    private array $variables = [];
    private \Closure $twigClassExists;
    private \Closure $isReadable;
    private \Closure $fileReader;

    public function __construct(
        private readonly string $templatePath,
        private readonly ?base $block = null,
        ?callable $twigClassExists = null,
        ?callable $isReadable = null,
        ?callable $fileReader = null
    ) {
        $this->twigClassExists = \Closure::fromCallable(
            $twigClassExists ?? static fn(string $className): bool => class_exists($className)
        );
        $this->isReadable = \Closure::fromCallable(
            $isReadable ?? static fn(string $path): bool => is_readable($path)
        );
        $this->fileReader = \Closure::fromCallable(
            $fileReader ?? static fn(string $path): string|false => file_get_contents($path)
        );

        if ($block !== null) {
            $this->variables['block'] = $block;
            $this->variables['oBlock'] = $block;
            $this->variables['tab'] = $block->getTab();
            $this->variables['oTab'] = $block->getTab();
        }
    }

    public function assign(string $key, mixed $value): void
    {
        $this->variables[$key] = $value;
    }

    public function fetch(): string
    {
        if (!$this->isReadable($this->templatePath)) {
            throw new \RuntimeException('Twig template file is not readable at "' . $this->templatePath . '".');
        }
        if (!$this->twigClassesAvailable()) {
            throw new \RuntimeException('Twig is not installed. Run "php composer.phar install" on the deployed application so vendor/autoload.php can load twig/twig.');
        }

        $loader = new ArrayLoader([
            $this->templatePath => (string)$this->readFile($this->templatePath),
        ]);
        $twig = new Environment($loader, [
            'autoescape' => false,
            'cache' => false,
            'strict_variables' => false,
        ]);
        $twig->addFunction(new TwigFunction('msg', static fn(string $key): string => $key));

        return $twig->render($this->templatePath, $this->variables);
    }

    private function twigClassesAvailable(): bool
    {
        foreach ([ArrayLoader::class, Environment::class, TwigFunction::class] as $className) {
            if (!$this->twigClassExists($className)) {
                return false;
            }
        }

        return true;
    }

    private function twigClassExists(string $className): bool
    {
        return ($this->twigClassExists)($className);
    }

    private function isReadable(string $path): bool
    {
        return ($this->isReadable)($path);
    }

    private function readFile(string $path): string|false
    {
        return ($this->fileReader)($path);
    }
}
