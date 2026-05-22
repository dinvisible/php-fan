<?php

declare(strict_types=1);

namespace fan\app\__tools\design;
/**
 * db_connection_subnav block for tools
 * @version 05.02.006 (20.04.2015)
 */
class db_connection_subnav extends \fan\project\block\common\simple
{
    protected array $nav = [];

    protected ?string $current = null;

    public function init(): void
    {
        $req = $this->getRequest();
        $mainKey = implode('/', $req->getAll('M'));
        $this->current = $req->get(0, 'A');

        $conf = $this->containerService('config')->get('database');
        foreach ($conf['DATABASE'] as $k => $v) {
            $this->nav[$k] = [
                'url'  => $this->tab->getURI('~/' . $mainKey . '/' . $k . '.html', 'link', null, null),
                'name' => 'DB <b>' . $k . '</b> (<i>' . $v['DATABASE'] . '</i>)',
            ];
        }

        $this->view->nav    = $this->nav;
        $this->view->current = $this->current;
    }

    public function getCurrentName(): ?string
    {
        return $this->nav[$this->current]['name'] ?? null;
    }

}
