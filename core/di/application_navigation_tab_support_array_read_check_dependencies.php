<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_support_array_read_check_dependencies
{
    private application_navigation_tab_array_value_reader_read_check_dependencies $arrayValueReader;
    private application_navigation_tab_array_like_checker_read_check_dependencies $arrayLikeChecker;

    public function __construct(container_interface $container)
    {
        $this->arrayValueReader = new application_navigation_tab_array_value_reader_read_check_dependencies($container);
        $this->arrayLikeChecker = new application_navigation_tab_array_like_checker_read_check_dependencies($container);
    }

    public function arrayValueReader(): mixed
    {
        return $this->arrayValueReader->arrayValueReader();
    }

    public function arrayLikeChecker(): mixed
    {
        return $this->arrayLikeChecker->arrayLikeChecker();
    }
}
