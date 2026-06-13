<?php

declare(strict_types=1);

namespace fan\core\block;

use fan\core\di\container_interface;

final class base_dependency_array_read_helper_group
{
    private base_dependency_array_value_reader_helper_group $valueReader;
    private base_dependency_array_like_checker_helper_group $likeChecker;

    public function __construct(container_interface $container)
    {
        $this->valueReader = new base_dependency_array_value_reader_helper_group($container);
        $this->likeChecker = new base_dependency_array_like_checker_helper_group($container);
    }

    public function dependencies(): array
    {
        return array_merge(
            $this->valueReader->dependencies(),
            $this->likeChecker->dependencies()
        );
    }
}
