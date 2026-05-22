<?php

declare(strict_types=1);

namespace fan\app\__tools\design;
/**
 * counter_subnav block for tools
 * @version 05.02.005 (12.02.2015)
 */
class counter_subnav extends \fan\project\block\common\simple
{
    protected ?string $mainKey = null;
    public function init(): void
    {
        $tab = $this->tab;

        $mainRequest = $this->getRequest()->getAll('M');;
        $this->mainKey = $mainRequest[0];
        $current = array_val($mainRequest, 1);

        $this->view->current = $current;
    }

    public function getNavUrl(string $key, string $addUrl = ''): string
    {
        return $this->tab->getURI('~/' . $this->mainKey . '/' . $key . $addUrl . '.html', 'link', null, null);
    }

}
