<?php

declare(strict_types=1);

namespace fan\core\block\admin;
use fan\core\base\model\rowset;
use fan\core\block\loader\base as loader_base;
use fan\core\view\router;

/**
 * Base class for loader block
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
 * @version of file: 05.02.005 (12.02.2015)
 * @abstract
 */
abstract class base extends loader_base
{
    public function getHashArray(array $arg, ?array $arrMerge = null, mixed $mergeBefore = true): array
    {
        $retData =[];
        foreach ($this->getRowset($arg) as $e) {
            $retData[$e->get($arg['key'])] = $e->get($arg['val']);
        }
        if ($arrMerge) {
            $recursiveMerger = $this->recursiveMerger();
            $retData = $mergeBefore ? $recursiveMerger($arrMerge, $retData) : $recursiveMerger($retData, $arrMerge);
        }
        reset($retData);
        return [$retData, key($retData)];
    }

    public function getIncludedArray(): array
    {
        $retData =[];
        $retKeys =[];
        $args = func_get_args();

        $argPrev = array_shift($args);
        if (isset($argPrev['data'])) {
            foreach ($argPrev['data'] as $k => $v) {
                $retData[$k] = ['val' => $v];
            }
        } else {
            $tmp = $this->getRowset($argPrev);
            foreach ($tmp as $e) {
                /* @var $e \fan\core\base\model\row */
                $retData[$e->get($argPrev['key'])] = ['val' => $e->get($argPrev['val'])];
            }
        }
        if (!empty($argPrev['select'])) {
            $retKeys[0] = $argPrev['select'];
        }

        if ($retData) {
            $depth = 1;

            $parent =& $retData;
            foreach ($args as $k => $arg) {
                $interim[$k] = [];

                if (empty($arg['parent'])) {
                    $arg['parent'] = $argPrev['key'];
                }
                $param = [$arg['parent'] => array_keys($parent)];
                $arg['param'] = isset($arg['param']) ? array_merge($param, $arg['param']) : $param;

                $tmp = $this->getRowset($arg);
                if (!$tmp) {
                    break;
                }
                foreach ($tmp as $e) {
                    $k0 = $e->get($arg['parent']);
                    $k1 = $e->get($arg['key']);
                    if (isset($parent[$k0])) {
                        $interim[$k][$k1] = ['val' => $e->get($arg['val'])];
                        $parent[$k0]['child'][$k1] =& $interim[$k][$k1];
                    }
                }

                if (!empty($arg['select'])) {
                    $retKeys[$depth] = $arg['select'];
                }

                $argPrev = $arg;
                $parent =& $interim[$k];
                $depth++;
            }
            $this->defineRetKeys($retKeys, $retData, $depth, 0);
            ksort($retKeys);
        } else {
            $depth = 0;
        }

        return [$retData, $retKeys, $depth];
    }

    private function getRowset(array $arg): rowset
    {
        if (!isset($arg['param'])) {
            $arg['param'] = null;
        }
        if (!isset($arg['qtt'])) {
            $arg['qtt'] = -1;
        }
        if (!isset($arg['offset'])) {
            $arg['offset'] = -1;
        }
        if (!isset($arg['order'])) {
            $arg['order'] = '';
        }

        $ett = $this->entityService()->get((string)$arg['entity']);
        if (isset($arg['sql_key'])) {
            return $ett->getRowsetByKey(
                (string)$arg['sql_key'],
                $arg['param'],
                (int)$arg['qtt'],
                (int)$arg['offset'],
                (string)$arg['order']
            );
        }
        return $ett->getRowsetByParam($arg['param'], (int)$arg['qtt'], (int)$arg['offset'], (string)$arg['order']);
    }


    private function defineRetKeys(&$retKeys, array $retData, int $depth, int $i): bool
    {
        if (array_key_exists($i, $retKeys)) {
            if ($depth <= 1) {
                return true;
            }
            $v = $retData[$retKeys[$i]]['child'] ?? null;
            return $v && $this->defineRetKeys($retKeys, $v, $depth - 1, $i + 1);
        }
        foreach ($retData as $k => $v) {
            if ($depth <= 1 || (!empty($v['child']) && $this->defineRetKeys($retKeys, $v['child'], $depth - 1, $i + 1))) {
                $retKeys[$i] = $k;
                return true;
            }
        }
        return false;
    }

    protected function setTemplateVar(string $key, mixed $value): static
    {
        if ($key === 'json') {
            $this->setJson($value);
        } elseif ($key === 'text') {
            $this->setText((string)$value);
        } else {
            $this->view[$key] = $value;
        }
        return $this;
    }


    protected function getTemplateCode(array $addVars = []): string
    {
        $retHtml  = '';
        $tmp      = $this->view instanceof router && count($this->view) > 0 ? $this->view->toArray() : [];
        $tplVars  = isset($tmp['html']) && is_array($tmp['html']) ? $tmp['html'] : [];
        $template = $this->getTemplate();
        if (!empty($template)) {

            // Transfer template value from meta-data
            $metaTplVar = $this->getMeta('tplVars');
            if (is_array($metaTplVar)) {
                foreach ($metaTplVar as $k => $v) {
                    if (!isset($tplVars[$k])) {
                        $tplVars[$k] = $v;
                    }
                }
            }

            $tplParentClass = $this->getMeta('tpl_parent_class');
            $template = $this->templateService()->get((string)$template, is_null($tplParentClass) ? null : (string)$tplParentClass, $this);
            foreach ($tplVars as $k => $v) {
                $template->assign((string)$k, $v);
            }
            foreach ($addVars as $k => $v) {
                $template->assign((string)$k, $v);
            }
            $retHtml = $template->fetch();

        } elseif (!empty($tplVars)) {
            foreach ($tplVars as $v) {
                if (is_scalar($v)) {
                    $retHtml .= (string)$v;
                }
            }
        }
        return $retHtml;
    }

}
