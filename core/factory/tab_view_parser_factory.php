<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\block\base;
use fan\core\view\parser\loader;


final class tab_view_parser_factory
{
    private \Closure $configuredServiceFactory;

    public function __construct(callable $configuredServiceFactory)
    {
        $this->configuredServiceFactory = \Closure::fromCallable($configuredServiceFactory);
    }

    public function __invoke(
        string $viewParserClass,
        int $debugMode,
        base $mainBlock,
        callable $jsonFactory,
        ?object $debug,
        callable $templateFactory,
        object $header,
        object $locale,
        callable $dataLoaderFactory
    ): object {
        if ($debugMode > 0) {
            return ($this->configuredServiceFactory)($viewParserClass, [
                $mainBlock,
                $jsonFactory,
                $debug,
                $templateFactory,
                $header,
                $locale
            ]);
        }
        if (is_a($viewParserClass, loader::class, true)) {
            return ($this->configuredServiceFactory)($viewParserClass, [
                $mainBlock,
                $jsonFactory,
                $templateFactory,
                $header,
                $locale,
                $dataLoaderFactory
            ]);
        }

        return ($this->configuredServiceFactory)($viewParserClass, [
            $mainBlock,
            $jsonFactory,
            $templateFactory,
            $header,
            $locale
        ]);
    }

}
