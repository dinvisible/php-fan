<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_content_localization_context_dependencies
{
    private application_content_locale_context_dependencies $locale;
    private application_content_tab_factory_context_dependencies $tabFactory;

    public function __construct(container_interface $container)
    {
        $this->locale = new application_content_locale_context_dependencies($container);
        $this->tabFactory = new application_content_tab_factory_context_dependencies($container);
    }

    public function locale(): object
    {
        return $this->locale->locale();
    }

    public function tabFactory(): callable
    {
        return $this->tabFactory->tabFactory();
    }
}
