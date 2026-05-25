<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\block\base;
use fan\core\view\parser\html;
use fan\core\view\parser\json;
use fan\core\view\parser\loader;
use fan\core\view\router;
use fan\core\view\router\loader_state;
use fan\project\view\router\html as router_html;
use fan\project\view\router\json as router_json;
use fan\project\view\router\loader as router_loader;
use fan\project\view\router\simple;


final class view_router_factory
{
    private \Closure $keeperFactory;
    private \Closure $arrayAdducer;

    public function __construct(?callable $keeperFactory = null, ?callable $arrayAdducer = null)
    {
        $this->keeperFactory = \Closure::fromCallable($keeperFactory ?? new view_keeper_factory());
        $this->arrayAdducer = \Closure::fromCallable($arrayAdducer ?? static fn(mixed $value): array => \adduceToArray($value));
    }

    public function __invoke(
        string $viewParserClass,
        base $block,
        ?loader_state $loaderState = null,
        ?callable $blockExceptionFactory = null
    ): router {
        if (is_a($viewParserClass, loader::class, true)) {
            if ($loaderState === null) {
                throw new \RuntimeException('Loader state is not configured for loader view router.');
            }

            return new router_loader($block, $loaderState, $this->keeperFactory, $blockExceptionFactory, $this->arrayAdducer);
        }
        if (is_a($viewParserClass, html::class, true)) {
            return new router_html($block, $this->keeperFactory, $blockExceptionFactory, $this->arrayAdducer);
        }
        if (is_a($viewParserClass, json::class, true)) {
            return new router_json($block, $this->keeperFactory, $blockExceptionFactory, $this->arrayAdducer);
        }

        return new simple($block, $this->keeperFactory, $blockExceptionFactory, $this->arrayAdducer);
    }
}
