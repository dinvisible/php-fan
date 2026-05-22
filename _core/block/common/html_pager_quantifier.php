<?php

declare(strict_types=1);

namespace fan\core\block\common;
/**
 * Pager quantifier class
 *
 * This file is part PHP-FAN (php-framework of Alexandr Nosov)
 * Copyright (C) 2005-2007 Alexandr Nosov, http://www.alex.4n.com.ua/
 *
 * Licensed under the terms of the GNU Lesser General Public License:
 *     http://www.opensource.org/licenses/lgpl-license.php
 *
 * Do not remove this comment if you want to use script!
 * Не удаляйте данный комментарий, если вы хотите использовать скрипт!
 *
 * @author: Alexandr Nosov (alex@4n.com.ua)
 * @version of file: 05.02.006 (20.04.2015)
 */
class html_pager_quantifier extends \fan\core\block\form\usual
{
    public function init(): void
    {
        $gurrentGet = [];
        $get = $this->containerService('request')->getAll('G', []);
        foreach ($get as $k => $v) {
            if ((string)$k !== 'pager_quantifier') {
                $gurrentGet[$k] = $v;
            }
        }
        $this->_setViewVar('aGurrentGet', $gurrentGet);

        $this->_parseForm(true, !empty($get['pager_quantifier']));
    }

    protected function onSubmit(): void
    {
        $this->container->setElmPerPage((int)$this->fieldValue['pager_quantifier']);
    }

    public function getDynamicMeta(mixed $meta): array
    {
        $srcData = $this->container->getMeta('quantifier', []);

        $formData = [];

        $metaFormData = explode(',', (string)$srcData['values']);
        foreach ($metaFormData as $v) {
            $v = trim($v);
            $formData[] = [
                'value' => $v,
                'text'  => $v,
            ];
        }

        return [
            'form'  => [
                'fields'    => [
                    'pager_quantifier'  => [
                        'label'         => (string)$srcData['label'],
                        'data'          => $formData,
                        'default_value' => $this->container->getElmPerPage(),
                    ],
                ],
            ],
        ];
    }

    public function getParentMeta(): array
    {

        $fileMeta = $this->readMetaFile(substr(__FILE__, 0, -3) . 'meta.php');
        return array_merge_recursive_alt(parent::getParentMeta(), $fileMeta);

    }
}
