<?php
declare(strict_types=1);

namespace fan\core\base\model\spec_file\image;
use fan\core\base\model\rowset;
use fan\core\base\model\spec_file\entity as spec_file_entity;

/**
 * Entity of image file
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
 * @version of file: 05.02.006 (20.04.2015)
 * @abstract
 */
abstract class entity extends spec_file_entity
{
    protected string $imgRegExp = '/\{IMG(?:_(\d+)|-(\d+))\s*(.*?)\}/is';

    protected string $advImgRegExp = '/\{(IMG|NAIL|LINK|BLOWUP1|BLOWUP2)(?:_(\d+)|-(\d+))(?:\[(\d+)*(\d+)\])\s*(.*?)\}/is';

    public function rotateImageById(int|float $id, int|float $angle): void
    {
        $row = $this->getRowById($id);
        $row->rotateImage($angle);
    }

    public function getImgTagById(int|float $id, string $cssClass = '', ?array $param = null): ?string
    {
        $row = $this->getRowById($id);
        return $row->getImgTag($cssClass, $param);
    }

    public function getImgTagByCode(string $code, ?array $param = null): ?string
    {
        $matches = null;
        preg_match($this->imgRegExp, $code, $matches);
        $row = $this->getRowById($matches[1]);
        return $row->getImgTag($matches[2], $param);
    }

    public function replaceCodeToImgTag(string $code, ?array $linkTbl = NULL, string $keyField = 'id_file_data'): string
    {
        $matches = null;
        if (preg_match_all($this->imgRegExp, $code, $matches)) {
            $repl = [];
            $pos  = [
                'id'    => 1,
                'num'   => 2,
                'class' => 3,
            ];
            $ett = $this->prepareImgEtt($repl, $matches, $pos, $linkTbl, $keyField, false);

            // Replace image code
            foreach ($ett as $e) {
                foreach ($repl[$e->getId()] as $v) {
                    $code = str_replace($v[0], $e->getImgTag($v[1]), $code);
                }
            }
        }
        return $code;
    }


    public function advReplaceCodeToImgTag(string $code, array $param, ?array $linkTbl = NULL, string $keyField = 'id_file_data'): string
    {
        $matches = null;
        if (preg_match_all($this->advImgRegExp, $code, $matches)) {
            $repl = [];
            $pos  = [
                'type'   => 1,
                'id'     => 2,
                'num'    => 3,
                'width'  => 4,
                'height' => 5,
                'class'  => 6,
            ];
            $ett = $this->prepareImgEtt($repl, $matches, $pos, $linkTbl, $keyField, true);

            // Replace image code
            foreach ($ett as $e) {
                foreach ($repl[$e->getId()] as $v) {
                    $v[2] = strtolower($v[2]);
                    $paramTmp = $param;
                    if (!empty($v[1])) {
                        $k = (string)$v[2] === 'img' || (string)$v[2] === 'nail' ? $v[2] : 'div';
                        $paramTmp[$k]['class'] = empty($paramTmp[$k]['class']) ? $v[3] : $paramTmp[$k]['class'] . ' ' . $v[3];
                    }
                    if (!empty($v[3])) {
                        $paramTmp['nail']['width'] = $v[3];
                    }
                    if (!empty($v[4])) {
                        $paramTmp['nail']['height'] = $v[4];
                    }
                    $code = str_replace($v[0], $e->advGetImgTag($v[2], $paramTmp), $code);
                }
            }
        }
        return $code;
    }

    private function prepareImgEtt(&$repl, array $matches, array $pos, ?array $linkTbl, string $keyField, bool $adv): rowset
    {
        // Define by ID
        foreach ($matches[$pos['id']] as $k => $id) {
            if ($id) {
                $repl[$id][0] = [$matches[0][$k], $matches[$pos['class']][$k]];
                if ($adv){
                    $repl[$id][0][2] = $matches[$pos['type']];
                    $repl[$id][0][3] = $matches[$pos['width']] ?? null;
                    $repl[$id][0][4] = $matches[$pos['height']] ?? null;
                }
            }
        }

        // Define by NUM
        if ($linkTbl) {
            foreach ($matches[$pos['num']] as $k => $n) {
                if ($n) {
                    $row = $this->createRelatedEntityRow($linkTbl[0])->loadByParam([
                        $linkTbl[1] => $linkTbl[2],
                        'order_num'  => $n,
                    ]);
                    if ($row->checkIsLoad()) {
                        $repl[$row->id_file_data][1] = [$matches[0][$k], $matches[$pos['class']][$k]];
                        if ($adv){
                            $repl[$id][0][2] = $matches[$pos['type']];
                            $repl[$id][0][3] = $matches[$pos['width']] ?? null;
                            $repl[$id][0][4] = $matches[$pos['height']] ?? null;
                        }
                    }
                }
            }
        }

        // Get entity image list
        return $this->getRowsetByParam($keyField . ' IN(' . implode(',', array_keys($repl)) . ')');
    }

}
