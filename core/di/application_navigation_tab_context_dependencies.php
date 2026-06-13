<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_context_dependencies
{
    private application_navigation_tab_routing_context_dependencies $routing;
    private application_navigation_tab_locale_session_context_dependencies $localeSession;
    private application_navigation_tab_input_context_dependencies $input;

    public function __construct(container_interface $container)
    {
        $this->routing = new application_navigation_tab_routing_context_dependencies($container);
        $this->localeSession = new application_navigation_tab_locale_session_context_dependencies($container);
        $this->input = new application_navigation_tab_input_context_dependencies($container);
    }

    public function matcher(): mixed
    {
        return $this->routing->matcher();
    }

    public function request(): mixed
    {
        return $this->routing->request();
    }

    public function locale(): mixed
    {
        return $this->localeSession->locale();
    }

    public function sessionFactory(): callable
    {
        return $this->localeSession->sessionFactory();
    }

    public function requestInput(): mixed
    {
        return $this->input->requestInput();
    }
}
