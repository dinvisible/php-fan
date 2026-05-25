<?php

declare(strict_types=1);

namespace fan\core\di;
use fan\core\view\router\loader_state;


final class view_loader_state_factory
{
    private \Closure $jsonKeeperFactory;

    private \Closure $textKeeperFactory;

    public function __construct(?callable $jsonKeeperFactory = null, ?callable $textKeeperFactory = null)
    {
        $this->jsonKeeperFactory = \Closure::fromCallable($jsonKeeperFactory ?? new view_loader_json_keeper_factory());
        $this->textKeeperFactory = \Closure::fromCallable($textKeeperFactory ?? new view_loader_text_keeper_factory());
    }

    public function __invoke(): loader_state
    {
        return new loader_state($this->jsonKeeperFactory, $this->textKeeperFactory);
    }
}
