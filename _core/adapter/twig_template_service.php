<?php

declare(strict_types=1);

namespace fan\core\adapter;

use fan\core\block\base;
use Twig\Environment;
use Twig\TwigFunction;
use Twig\Loader\ArrayLoader;

final class twig_template_service
{
    public function get(string $templatePath, mixed $parent = null, ?base $block = null): object
    {
        return new twig_template_file($templatePath, $block);
    }
}

final class twig_template_file
{
    private array $variables = [];

    public function __construct(
        private readonly string $templatePath,
        private readonly ?base $block = null
    ) {
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
        if (!is_readable($this->templatePath)) {
            throw new \RuntimeException('Twig template file is not readable at "' . $this->templatePath . '".');
        }
        if (!class_exists(ArrayLoader::class) || !class_exists(Environment::class) || !class_exists(TwigFunction::class)) {
            throw new \RuntimeException('Twig is not installed. Run "php composer.phar install" on the deployed application so vendor/autoload.php can load twig/twig.');
        }

        $loader = new ArrayLoader([
            $this->templatePath => (string)file_get_contents($this->templatePath),
        ]);
        $twig = new Environment($loader, [
            'autoescape' => false,
            'cache' => false,
            'strict_variables' => false,
        ]);
        $twig->addFunction(new TwigFunction('msg', static fn(string $key): string => $key));

        return $twig->render($this->templatePath, $this->variables);
    }
}
