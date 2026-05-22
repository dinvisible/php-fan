<?php
declare(strict_types=1);

namespace fan\core\service\template\type;
/**
 *
 * This file is part PHP-FAN (php-framework from Alexandr Nosov)
 * Copyright (C) 2005-2007 Alexandr Nosov, http://www.alex.4n.com.ua/
 *
 * Licensed under the terms of the GNU Lesser General Public License:
 *     http://www.opensource.org/licenses/lgpl-license.php
 *
 * Do not remove this comment if you want to use script!
 * Не удаляйте данный комментарий, если вы хотите использовать скрипт!
 *
 * @author: Alexandr Nosov (alex@4n.com.ua)
 * @version of file: 05.02.001 (10.03.2014)
 */
abstract class image extends base
{
    /**
     * Basic parameters: src, width, height, alt, href
     * @var array
     */
    protected ?array $baseParam = null;

    public static function getEngineList(): array
    {
        return ['main'];
    }

    public static function getAutoParseTag(): array
    {
        return [
            'img'              => ['method' => 'makeImgTag'],
            'top_signature'    => ['method' => 'makeTopSign'],
            'bottom_signature' => ['method' => 'makeBotSign'],
        ];
    }

    public function setBaseParam(array $param): void
    {
        $this->baseParam = $param;
        $this->assign('sBaseClass', isset($param['class']) ? $param['class'] : null);
        $this->assign('sImgLnk', isset($param['link']['full_url']) ? $param['link']['full_url'] : null);
    }

    public function makeImgTag(array $data = []): string
    {
        $image = $this->baseParam['img'];
        $attr = '';
        foreach (['class', 'style', 'lang', 'dir', 'alt', 'title'] as $k) {
            if (isset($image[$k])) {
                $attr .= ' ' . $k . '="' . $image[$k] . '"';
            } elseif (isset($data[$k])) {
                $attr .= ' ' . $k . '="' . $data[$k] . '"';
            }
        }
        return '<img src="' . $image['full_url'] . '" width="' . $image['width'] . '" height="' . $image['height'] . '"' . $attr . ' />';
    }

    public function makeTopSign(array $data = []): string
    {
        return (string)($this->baseParam['signature']['position'] ?? '') === 'top' ? $this->makeTopSign($data) : '';
    }

    public function makeBotSign(array $data = []): string
    {
        return (string)($this->baseParam['signature']['position'] ?? '') === 'bottom' ? $this->makeTopSign($data) : '';
    }

    protected function makeSignature(array $data): string
    {
        return $this->baseParam['signature']['text'] ? '<span' . (!empty($data['class']) ? ' class="' . $data['class'] . '"' : '') . '>' . $this->baseParam['signature']['text'] . '</span>' : '';
    }
}
