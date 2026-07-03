<?php

declare(strict_types=1);

namespace fan\core\di;

final class application_navigation_tab_model_factory_dependencies
{
    private application_navigation_tab_data_model_factory_dependencies $data;
    private application_navigation_tab_media_model_factory_dependencies $media;
    private application_navigation_tab_user_time_model_factory_dependencies $userTime;

    public function __construct(container_interface $container)
    {
        $this->data = new application_navigation_tab_data_model_factory_dependencies($container);
        $this->media = new application_navigation_tab_media_model_factory_dependencies($container);
        $this->userTime = new application_navigation_tab_user_time_model_factory_dependencies($container);
    }

    public function entityFactory(): callable
    {
        return $this->data->entityFactory();
    }

    public function pagerFactory(): callable
    {
        return $this->data->pagerFactory();
    }

    public function databaseFactory(): callable
    {
        return $this->data->databaseFactory();
    }

    public function obfuscatorFactory(): callable
    {
        return $this->media->obfuscatorFactory();
    }

    public function imageModifyFactory(): callable
    {
        return $this->media->imageModifyFactory();
    }

    public function userFactory(): callable
    {
        return $this->userTime->userFactory();
    }

    public function dateFactory(): callable
    {
        return $this->userTime->dateFactory();
    }
}
