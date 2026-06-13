<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_locale_session_context_dependencies
{
    private application_navigation_tab_locale_context_dependencies $locale;
    private application_navigation_tab_session_factory_context_dependencies $sessionFactory;

    public function __construct(container_interface $container)
    {
        $this->locale = new application_navigation_tab_locale_context_dependencies($container);
        $this->sessionFactory = new application_navigation_tab_session_factory_context_dependencies($container);
    }

    public function locale(): mixed
    {
        return $this->locale->locale();
    }

    public function sessionFactory(): callable
    {
        return $this->sessionFactory->sessionFactory();
    }
}
